<?php

namespace DripFollowers\Service\Repo;

use DripFollowers\DripFollowers;
use DripFollowers\Common\PacksTypes;
use DripFollowers\Common\DripFollowersConstants;
use DripFollowers\Common\ProgressStatus;
use DripFollowers\Common\Pack;
use DripFollowers\Common\InstagramTypes;

class OrderRepository {
    private $_plugin;

    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
    }
    
    public function get_in_progress_daily_orders(){
        $args = array (
                'post_type' => DripFollowersConstants::CPT_DRIP_ORDER,
                'posts_per_page'=>-1,
                'meta_query' => array (
                        'relation' => 'AND',
                        array (
                                'key' => DripFollowersConstants::COL_ORDER_PAYMENT_STATUS,
                                'value' => DripFollowersConstants::STATUS_PAYMENT_VALID
                        ),
                        array (
                                'key' => DripFollowersConstants::COL_ORDER_PROGRESS,
                                'value' => ProgressStatus::IN_PROGRESS
                        ),
                        array (
                                'key' => DripFollowersConstants::COL_ORDER_SERVICE,
                                'value' => PacksTypes::Automatic_Followers 
                        ) 
                )
        );
        return $this->query_args_to_value_object ( $args, false );
    }
    
    public function get_re_scheduled_orders(){
        $args = array (
                'post_type' => DripFollowersConstants::CPT_DRIP_ORDER,
                'posts_per_page'=>-1,
                'meta_query' => array (
                        'relation' => 'AND',
                        array (
                                'key' => DripFollowersConstants::COL_ORDER_PAYMENT_STATUS,
                                'value' => DripFollowersConstants::STATUS_PAYMENT_VALID
                        ),
                        array (
                                'key' => DripFollowersConstants::COL_ORDER_PROGRESS,
                                'value' => ProgressStatus::RE_SCHEDULED
                        )
                )
        );
        return $this->query_args_to_value_object ( $args, true );
    }

    public function get_executed_orders() {
        $orders = array ();
        
        $args = array (
                'post_type' => DripFollowersConstants::CPT_DRIP_ORDER,
                'posts_per_page'=>-1,
                'date_query' => array (
                        array (
                                'column' => 'post_date_gmt',
								'after' => '1 month ago'
                        ) 
                ),
                'meta_query' => array (
                        'relation' => 'AND',
                        array (
                                'key' => DripFollowersConstants::COL_ORDER_PAYMENT_STATUS,
                                'value' => DripFollowersConstants::STATUS_PAYMENT_VALID 
                        ),
                        array (
                                'key' => DripFollowersConstants::COL_ORDER_PROGRESS,
                                'value' => ProgressStatus::IN_PROGRESS 
                        ),
                        array (
                                'key' => DripFollowersConstants::COL_ORDER_SERVICE,
                                'value' => PacksTypes::Automatic_Followers,
                                'compare' => '!=' 
                        )
                ) 
        );
        $orders1 = $this->query_args_to_value_object ( $args, true );
        //$this->_plugin->get_logger()->addDebug('OrderRepository - $orders1', array($orders1));
        
        $args = array (
                'post_type' => DripFollowersConstants::CPT_DRIP_ORDER,
                'posts_per_page'=>-1,
                'date_query' => array (
                        array (
                                'column' => 'post_date_gmt',
                                'before' => '1 month ago',
								'after' => '2 months ago'
                        ) 
                ),
                'meta_query' => array (
                        'relation' => 'AND',
                        array (
                                'key' => DripFollowersConstants::COL_ORDER_PAYMENT_STATUS,
                                'value' => DripFollowersConstants::STATUS_PAYMENT_VALID 
                        ),
                        array (
                                'key' => DripFollowersConstants::COL_ORDER_PROGRESS,
                                'value' => ProgressStatus::IN_PROGRESS 
                        ),
                        array (
                                'key' => DripFollowersConstants::COL_ORDER_SERVICE,
                                'value' => PacksTypes::Automatic_Followers
                        ) 
                ) 
        );
        $orders2 = $this->query_args_to_value_object ( $args, true ) ;
        //$this->_plugin->get_logger()->addDebug('OrderRepository - $orders2', array($orders2));
        
        $orders = $orders1 + $orders2;
        
        //$this->_plugin->get_logger ()->addDebug ( 'OrderRepository - get_executed_orders result', array ($orders) );
        return $orders;
    }
    
    public function get_order_by_id($orderId){
        $args = array (
            'p' => $orderId,
            'post_type' => DripFollowersConstants::CPT_DRIP_ORDER,
        );
        $orders = $this->query_args_to_value_object ( $args, false ) ;
        if(!empty($orders)){
            return $orders[0];
        }
    }

    private function query_args_to_value_object($args, $asso) {
        $orders = array ();
        $the_query = new \WP_Query ( $args );
        if ($the_query->have_posts ()) {
            while ( $the_query->have_posts () ) {
                $the_query->the_post ();
                $order = new \stdClass ();
                $id = get_the_ID ();
                $order->id = $id;
                $task_id = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_TASK_ID, true );
                $order->task_id = $task_id;
                $order->contact_email = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_CONTACT_EMAIL, true );
                $order->customer = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_CUSTOMER, true );
                $order->type = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_SERVICE, true );
                $order->target = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_TARGET, true );
                $order->provider = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_PROVIDER, true );
				if (empty($order->provider))
					$order->provider = 'test';
                $order->with_upsell = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_WITH_UPSELL, true );
                $order->date = get_the_date ();
                if($asso){
                    $orders ['task_'.$task_id] = $order;
                } else {
                    $orders [] = $order;
                }
            }
        }
        wp_reset_postdata ();
        return $orders;
    }
    
    public function get_extra_info_by_order($orderId){
        $order = $this->get_order_by_id($orderId);
        $extraInfo['email'] = $order->contact_email;
        $extraInfo['paypal_email'] = $order->customer;
        $extraInfo['target'] = $order->target;
        return $extraInfo;
    }
    
    public function get_orders_for_export($options=null){
		set_time_limit(500);
        $args = array (
                'post_type' => DripFollowersConstants::CPT_DRIP_ORDER,
                'posts_per_page'=>-1
        );
        if(is_array($options) && isset($options['m'])){
            $args['m'] = $options['m'];
        }
        
        $orders = array ();
        $the_query = new \WP_Query ( $args );
        if ($the_query->have_posts ()) {
            while ( $the_query->have_posts () ) {
                $the_query->the_post ();
                $order = new \stdClass ();
                $id = get_the_ID ();
                $order->id = $id;
                $order->date = get_the_date ('Y/m/d G:i:s');
                $order->contact_email = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_CONTACT_EMAIL, true );
                $order->customer = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_CUSTOMER, true );
                $order->service = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_SERVICE, true );
                $order->pack = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_PACK, true );
                $order->number = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_NUMBER, true );
                $order->initial_count = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_INITIAL_COUNT, true );
                $order->final_count = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_FINAL_COUNT, true );
                $order->with_upsell = (get_post_meta ( $id, DripFollowersConstants::COL_ORDER_WITH_UPSELL, true )==true?'Yes':'No');
                $order->target = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_TARGET, true );
                $order->payment_status = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_PAYMENT_STATUS, true );
                $order->payment_amount = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_PAYMENT_AMOUNT, true );
                $order->trx_id = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_PAYMENT_TRX_ID, true );
                $order->task_id = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_TASK_ID, true );;
                $order->progress = get_post_meta ( $id, DripFollowersConstants::COL_ORDER_PROGRESS, true );
                $remarks = str_replace(",", "*", get_post_meta ( $id, DripFollowersConstants::COL_ORDER_REMARKS, true ));
                $remarks = str_replace("\n", "|", $remarks);
                $order->remarks = $remarks;
                $orders [] = $order;
            }
        }
        wp_reset_postdata ();
        return $orders;
    }
    
    public function save_order_current_count($order_id, $target, $pack_type, $stage){
    	$this->_plugin->get_logger()->addDebug("OrderRepository - Trying to save order count", array($order_id, $target, $pack_type, $stage));
    	try {
    		$column = $stage==DripFollowersConstants::ORDER_COUNT_INITIAL_STAGE?DripFollowersConstants::COL_ORDER_INITIAL_COUNT:DripFollowersConstants::COL_ORDER_FINAL_COUNT;
    		$target_type = $pack_type==PacksTypes::Instant_Likes?InstagramTypes::INSTAGRAM_MEDIA:InstagramTypes::INSTAGRAM_PROFILE;
    		$init_count = $this->_plugin->instagramInfoChecker->get_target_count($target, $target_type);
    		$this->_plugin->get_logger()->addDebug("OrderRepository - Save order with count", array($init_count, $order_id, $target, $pack_type, $stage));
    		update_post_meta ( $order_id, $column, $init_count );
    	} catch (\Exception $e){
    		$this->_plugin->get_logger()->addError("OrderRepository - Error while Saving initial count", array($order_id, $target, $pack_type, $stage));
    	}
    }
	public function is_duplicate_trx_id($trx_id) {
		$args = array (
				'post_type' => DripFollowersConstants::CPT_DRIP_ORDER,
				'meta_query' => array (
						array (
								'key' => DripFollowersConstants::COL_ORDER_PAYMENT_TRX_ID,
								'value' => $trx_id 
						) 
				) 
		);
		$query = new \WP_Query ( $args );
		$unique = $query->have_posts ();
		$this->_plugin->get_logger()->addDebug('OrderRepository - is trx_id duplicate', array($trx_id, $unique));
		return $unique;
	}
}