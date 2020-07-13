<?php

namespace DripFollowers\Service;

use DripFollowers\Common\DripFollowersConstants;
use DripFollowers\Service\Notifier\InstagramStartNotifier;
use Mdb\PayPal\Ipn\Event\MessageInvalidEvent;
use Mdb\PayPal\Ipn\Event\MessageVerificationFailureEvent;
use Mdb\PayPal\Ipn\Event\MessageVerifiedEvent;
use Mdb\PayPal\Ipn\ListenerBuilder\Guzzle\InputStreamListenerBuilder as ListenerBuilder;
session_start();

class PayPalIpnHandler {
    private $_logger;
    private $_plugin;

    public function __construct($plugin) {
        $this->_plugin = $plugin;
        $this->_logger = $plugin->get_logger ();
        
        add_action ( 'wp_ajax_paypal_ipn_listener', array (&$this, 'paypal_listner' ) );
        add_action ( 'wp_ajax_nopriv_paypal_ipn_listener', array (&$this, 'paypal_listner' ) );
    }

    public function get_base_plugin() {
        return $this->_plugin;
    }

    public function paypal_listner() {
        $self = $this;
        $plugin = $this->_plugin;
        $logger = $this->_logger;
		try {
            $logger->addInfo ( 'PayPalIpnHandler - NEW PayPal IPN' );
            $listenerBuilder = new ListenerBuilder();
            //Sandbox or Prod ?
            if ($this->_plugin->get_setting ( DripFollowersConstants::OPT_SANDBOX )) {
                $logger->addDebug ( 'PayPalIpnHandler - Using sandbox mode' );
                $listenerBuilder->useSandbox();
            } else {
                $logger->addDebug ( 'PayPalIpnHandler - Using production mode' );
            }
            //
            //$logger->addDebug ( 'PayPalIpnHandler - $_REQUEST Content', array($_REQUEST) );
            $listener = $listenerBuilder->build();
            //
            //
            $listener->onVerified(function (MessageVerifiedEvent $event) use ($self, $plugin, $logger) {
                $logger->addInfo ( 'PayPalIpnHandler - MessageVerifiedEvent' );
                $ipnMessage = $event->getMessage();
                // IPN message was verified, everything is ok!
                $logger->addDebug ( 'PayPalIpnHandler - MessageVerifiedEvent Content', array($ipnMessage) );
                // Enough fun, Doing our business now
                if(!$self->is_ipn_a_payment($ipnMessage)){
                    $logger->addDebug ( 'PayPalIpnHandler - JUST Notification for '.$ipnMessage->get('txn_type') );
                    return ;
                }
                if($plugin->orderRepo->is_duplicate_trx_id($ipnMessage->get('txn_id'))){
                    $logger->addError ( 'PayPalIpnHandler - DUPLICATE TRX ID '.$ipnMessage->get('txn_id') );
                    return ;
                }
                //
                $extraInfo = wp_parse_args ( $ipnMessage->get('custom') );
				if (isset($extraInfo['base64']))
					$extraInfo = wp_parse_args(base64_decode($extraInfo['base64']));
				
                $pack = $plugin->packRepo->get_pack ( $extraInfo ['pack'], $extraInfo ['code'] );
                $pack->set_with_upsell($extraInfo ['with_upsell']=='true');
                $logger->addDebug ( 'PayPalIpnHandler - IPN details', array ($pack, $extraInfo ) );
				if(isset($_REQUEST['paymentType']) && $_REQUEST['paymentType'] == 'discount')
				{
					$errors = array();
					$logger->addDebug ( 'PayPalIpnHandler - IPN is VALID' );
					$status = "VALID";
					$ch = curl_init(esc_url(admin_url('admin-post.php')).'?action=redeemDiscount&email='.$_REQUEST['email']);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$result = curl_exec($ch);
					$payment_amount = $this->extract_payment_amount ( $ipnMessage );
				}
				else
				{
					$errors = $self->check_paypal_payment ( $ipnMessage, $pack );
					$payment_amount = $this->extract_payment_amount ( $ipnMessage );
					$status = "INVALID";
					if (count ( $errors ) == 0) {
						$logger->addDebug ( 'PayPalIpnHandler - IPN is VALID' );
						$status = "VALID";
					} else {
						$logger->addDebug ( 'PayPalIpnHandler - IPN got some errors', array ($errors ) );
					}
				}
				
                $report = $self->build_paypal_payment_report ( $ipnMessage, $errors );
                $order_id = $this->insert_paypal_ipn ( $ipnMessage, $extraInfo, $pack, $status, $report, $errors );
                //
                if ($status == "VALID" && isset ( $order_id )) {
                    try {
                        $extraInfo ['paypal_email'] = $ipnMessage->get('receiver_email');
                        $startNotifier = new InstagramStartNotifier ( $plugin, $pack, $extraInfo ,$payment_amount );
						
                        $logger->addDebug ( 'PayPalIpnHandler - InstagramStartNotifier', array ($startNotifier ) );
                        $startNotifier->notify ();
                    } catch ( \Exception $e ) {
                        $logger->addError ( 'PayPalIpnHandler - InstagramStartNotifier Error', array ($e->getMessage () ) );
                    }
                    $instagramServiceProcessor = new InstagramServiceProcessor ( $plugin );
                    $instagramServiceProcessor->handle_ipn ( $order_id, $pack, $extraInfo );
                }
            });
            $listener->onInvalid(function (MessageInvalidEvent $event) use ($logger) {
                $logger->addInfo ( 'PayPalIpnHandler - MessageInvalidEvent' );
                $ipnMessage = $event->getMessage();
                // IPN message was was invalid, something is not right!
                $logger->addDebug ( 'PayPalIpnHandler - MessageInvalidEvent Content', array($ipnMessage) );
            });
            //
            //
            $listener->onVerificationFailure(function (MessageVerificationFailureEvent $event) use ($logger) {
                $logger->addError('PayPalIpnHandler - Listener Verification Failure');
                $error = $event->getError();
                $logger->addError('PayPalIpnHandler - Listener Verification Failure Content', array($error));
            });
            //**//
            $listener->listen();
            //**//
        } catch (\Exception $e) {
            $logger->addError('PayPalIpnHandler - Paypal Listner Exception', array($e->getMessage()));
        }
        exit ();
    }

    public function is_ipn_a_payment($message){
        $is_payment = false;
        $txn_type = $message->get('txn_type');
        if(isset($txn_type)){
            $valid_payments_type = array('cart', 'subscr_payment', 'recurring_payment', 'express_checkout', 'web_accept');
            if(in_array($txn_type, $valid_payments_type)){
                $is_payment = true;
            }
        }
        return $is_payment;
    }

    public function check_paypal_payment($message, $pack) {
        $errors = array ();
        if (strcmp ( $message->get('payment_status'), 'Completed' ) != 0)
            $errors [] = "Invalid Paypal payment status: " . $message->get('payment_status');
        
        if (strcmp ( $message->get('receiver_email'), $this->_plugin->get_setting ( DripFollowersConstants::OPT_PAYPAL_ACCOUNT ) ) != 0 && strcmp ( $message->get('receiver_email'), "ultimetech@hotmail.com") != 0)
            $errors [] = "Wrong Paypal receiver email: " . $message->get('receiver_email');
        
        if (strcmp ( $message->get('mc_currency'), 'USD' ) != 0)
            $errors [] = "Wrong Paypal payment currency: " . $message->get('mc_currency');
        
        if (! isset ( $pack )) {
            $errors [] = "Unknown pack info: " . $message->get('custom');
        } else {
            $payment_amount = $this->extract_payment_amount ( $message );
            $real_price = $pack->get_price();
            $debug ="COMPARING $payment_amount and $real_price ".PHP_EOL;
            if (!$this->are_floats_equal($payment_amount, $real_price) && $payment_amount < 1.00) {
					$errors [] = "Wrong Paypal payment amount  :$payment_amount, real price must be: " . $real_price . " for " . $pack->get_type() . "/" . $pack->get_code() . "/" . $pack->get_number () ."/". $pack->get_price() ;
					//DEBUG
					ob_start();
					echo '$payment_amount:'.PHP_EOL;
					var_dump($payment_amount);
					echo '--------'.PHP_EOL;
					echo '$real_price:'.PHP_EOL;
					var_dump($real_price);
					echo '--------'.PHP_EOL;
					$result = ob_get_clean();
					$this->_logger->addError('PayPalIpnHandler - WRONG PAYMENT AMOUNT', array($result, $real_price, $payment_amount));
					$admin_email = get_option( 'admin_email' );
					wp_mail( $admin_email, 'WRONG PAYMENT', $debug.$result );
					//DEBUG
				}
			
        }
        return $errors;
    }
    
    public function are_floats_equal($a, $b){
    	if (abs(($a-$b)/$b) < 0.00001) {
    		return true;
    	}
    	return false;
    }
    
    public function build_paypal_payment_report($messageData, $errors) {
        $report = "";
        foreach ( $messageData as $k => $v ) {
            $report .= $k . ' = ' . $v . "\n";
        }
        if (count ( $errors ) > 0) {
            foreach ( $errors as $error ) {
                $report .= 'error = ' . $error . "\n";
            }
        }
        return $report;
    }

    public function insert_paypal_ipn($message, $extraInfo, $pack, $status, $report, $errors) {
        $order = array (
            'post_type' => DripFollowersConstants::CPT_DRIP_ORDER,
            'post_status' => 'publish',
            'post_content' => $report 
        );
        $id = wp_insert_post ( $order, true );
        if (! is_wp_error ( $id )) {
            add_post_meta ( $id, DripFollowersConstants::COL_ORDER_PAYMENT_STATUS, $status );
            add_post_meta ( $id, DripFollowersConstants::COL_ORDER_CUSTOMER, $message->get('payer_email') );
            add_post_meta ( $id, DripFollowersConstants::COL_ORDER_CONTACT_EMAIL, $extraInfo ['email'] );
            add_post_meta ( $id, DripFollowersConstants::COL_ORDER_SERVICE, $extraInfo ['pack'] );
            add_post_meta ( $id, DripFollowersConstants::COL_ORDER_PACK, $extraInfo ['code'] );
            if (isset ( $pack )) {
                add_post_meta ( $id, DripFollowersConstants::COL_ORDER_NUMBER, $pack->get_number () );
            }
            add_post_meta ( $id, DripFollowersConstants::COL_ORDER_TARGET, $extraInfo ['target'] );
            add_post_meta ( $id, DripFollowersConstants::COL_ORDER_WITH_UPSELL, $extraInfo ['with_upsell'] == 'true' );
            add_post_meta ( $id, DripFollowersConstants::COL_ORDER_PAYMENT_AMOUNT, $this->extract_payment_amount ( $message ) );
            add_post_meta ( $id, DripFollowersConstants::COL_ORDER_PAYMENT_TRX_ID, $message->get('txn_id') );
            add_post_meta ( $id, DripFollowersConstants::COL_ORDER_PROGRESS, '-' );
            add_post_meta ( $id, DripFollowersConstants::COL_ORDER_REMARKS, implode ( "\n", $errors ) );
            
            return $id;
        }
    }

    public function extract_payment_amount($message) {
        $this->_logger->addDebug('PayPalIpnHandler - Received Amounts', array($message->get('payment_gross'), $message->get('mc_gross'),  $message->get('mc_gross_1'), $message->get('amount3'), $message->get('mc_amount3')));
        $amount = $message->get('payment_gross');
        if ('' == $amount) {
            $amount =  $message->get('mc_gross') ;
            if ('' == $amount) {
                $amount =  $message->get('mc_gross_1') ;
                if ('' == $amount) {
                    $amount =  $message->get('amount3') ;
                    if ('' == $amount) {
                        $amount =  $message->get('mc_amount3') ;
                    }
                }
            }
        }
        //
        $this->_logger->addDebug('PayPalIpnHandler - Extracted Amount', array($amount));
        return floatval($amount);
    }
}