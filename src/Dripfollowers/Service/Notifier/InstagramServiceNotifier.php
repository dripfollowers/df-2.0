<?php

namespace DripFollowers\Service\Notifier;

use DripFollowers\DripFollowers;
use DripFollowers\Common\Pack;
use DripFollowers\Common\PacksTypes;

abstract class InstagramServiceNotifier {
    protected $_plugin;
    protected $_pack;
    protected $_extraInfo;
    private $_from_name = 'FluidBuzz';
    private $_from_email = 'support@fluidbuzz.com';

    public function __construct(DripFollowers $plugin, Pack $pack, $extraInfo, $payment_amount) {
        $this->_plugin = $plugin;
        $this->_pack = $pack;
        $this->_extraInfo = $extraInfo;
        $this->paymentamount = $payment_amount;
    }

    private function get_recipients() {
        //$emails [] = $this->_extraInfo ['paypal_email'];
        if ($this->_extraInfo ['paypal_email'] != $this->_extraInfo ['email']) {
            $emails [] = $this->_extraInfo ['email'];
        }
        return $emails;
    }

    abstract function get_subject();

    abstract function get_email_body();

    public function notify() {
        $recipients = $this->get_recipients ();
        $subject = $this->get_subject ();
        $body = $this->get_email_header () . $this->get_email_body () . $this->get_email_footer ();
        $this->_plugin->get_logger ()->addDebug ( 'InstagramServiceNotifier - Sending Mail', array ($recipients,$subject,$body 
        ) );
        
        add_filter ( 'wp_mail_content_type', array ($this,'set_html_content_type' 
        ) );
        $headers[] = 'From: '.$this->_from_name.' <'.$this->_from_email.'>';
        $result = wp_mail ( $recipients, $subject, $body, $headers );
        if(!$result){
            $this->_plugin->get_logger ()->addDebug ( 'InstagramServiceNotifier - Sending WP Mail Error', array ($recipients,$subject,$body
            ) );
        } 
        remove_filter ( 'wp_mail_content_type', array ($this,'set_html_content_type' 
        ) );
        return $result;
    }

    private function get_email_header() {
        ob_start();
        ?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html>
        <head>
        <!-- If you delete this meta tag, the ground will open and swallow you. -->
        <meta name="viewport" content="width=device-width" />

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
        <title>ZURBemails</title>
        </head>
        <body topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">
        <!-- HEADER -->
        <table id='email-container' bgcolor="#eff2f6" style='width:500px;margin:0 auto;max-width:100%;background-color:#eff2f6;border-collapse: collapse;'><tbody><tr><td>
        <table style="background-image:url(https://fluidbuzz.com/wp-content/uploads/2019/09/emailHeaderFB.jpg);background-repeat:no-repeat;"><tbody><tr><td>
        <table style="width:100%;border-collapse: collapse;">
            <tr>
                <td></td>
                <td class="header container" align="">
                    
                    <!-- /content -->
                    <div class="content">
                        <table style="width:100%" >
                            <tr>
                                <td><img src="https://fluidbuzz.com/wp-content/uploads/2019/09/fluidbuzz_logo.png" style="display: block; margin: 20px auto;"/></td>
                            </tr>
                        </table>
                    </div><!-- /content -->
                    
                </td>
                <td></td>
            </tr>
        </table><!-- /HEADER -->

        <?php
        $content = ob_get_clean();
        return $content;
    }

    private function get_email_footer() {
        ob_start();
        ?>
        <!-- FOOTER -->
        <table width="100%">
                <tr>
                        <td align="center">
                                <p style="font-size: 14px; font-family: Helvetica, Arial, sans-serif;margin:20px 0;">
                                        <a href="https://fluidbuzz.com/frequently-asked-questions/">FAQ</a> |
                                        <a href="https://fluidbuzz.com/privacy-policy/">Privacy</a>
                                </p>
                        </td>
                </tr>
        </table>
        </td></tr></tbody></table>
        </td></tr></tbody></table>
        <?php
        $content = ob_get_clean();
        return $content;
    }

    protected function get_target_as_link() {
        $target = '';
        if ($this->_pack->get_type () == PacksTypes::Instant_Likes||$this->_pack->get_type () == PacksTypes::Instant_Views) {
            $target = '<a target="_blank" href="' . $this->_extraInfo ['target'] . '">' . $this->_extraInfo ['target'] . '</a>';
        } else {
            $target = '<a target="_blank" href="http://instagram.com/' . $this->_extraInfo ['target'] . '">' . $this->_extraInfo ['target'] . '</a>';
        }
        return $target;
    }

    function set_html_content_type() {
        return 'text/html';
    }
}