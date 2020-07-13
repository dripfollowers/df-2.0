<?php

namespace DripFollowers\Service;

use DripFollowers\DripFollowers;

class InstagramReTryReScheduledCron {
    private $_plugin;

    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
        
        add_action ( 'wp', array (&$this,'instagram_re_try_schedule' ) );
        add_action ( 'instagram_re_try_re_scheduled_cron', array (&$this,'re_try_re_scheduled_orders' ) );
    }

    function re_try_re_scheduled_orders() {
        try {
            $this->_plugin->get_logger ()->addDebug ( 'InstagramReTryReScheduledCron - Check existence of re scheduled orders' );
            $orders = $this->_plugin->orderRepo->get_re_scheduled_orders ();
            if(is_array($orders) && !empty($orders) ){
                $this->_plugin->get_logger ()->addDebug ( 'InstagramReTryReScheduledCron - Orders found ', array ($orders) );
                foreach ($orders as $order){
                    $pack = $this->_plugin->packRepo->get_pack_by_order($order->id);
                    $extraInfo['target'] = $order->target;
                    $instagramServiceProcessor = new InstagramServiceProcessor ( $this->_plugin );
                    $instagramServiceProcessor->handle_ipn ( $order->id, $pack, $extraInfo );
                }
            } else {
                $this->_plugin->get_logger ()->addDebug ( 'InstagramReTryReScheduledCron - no re scheduled orders found' );
            }
        } catch (\Exception $e) {
            $this->_plugin->get_logger ()->addError($e->getMessage());
        }
    }

    function instagram_re_try_schedule() {
        if (! wp_next_scheduled ( 'instagram_re_try_re_scheduled_cron' )) {
            wp_schedule_event ( time (), 'hourly', 'instagram_re_try_re_scheduled_cron' );
            //wp_schedule_event ( time (), '15minutely', 'instagram_re_try_re_scheduled_cron' );
        }
    }
}