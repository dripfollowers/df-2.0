<?php

namespace DripFollowers\Service;

use DripFollowers\Common\DripFollowersConstants;
use DripFollowers\Common\PacksTypes;
use DripFollowers\Service\Api\InstagramLikerService;
use DripFollowers\DripFollowers;

class InstagramGiftProcessor {
    
    const PHOTOS_TO_GIFT = 3;
    const EXPRESS_MAX_LIKES = 8;
    const EXPRESS_MIN_LIKES = 3;
    const DAILY_MAX_LIKES = 2;
    const DAILY_MIN_LIKES = 1;
    
    private $_plugin;

    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
        
        add_action ( 'wp', array (&$this,'daily_gifts_schedule') );
        add_action ( 'daily_gifts_cron', array (&$this, 'send_daily_gifts'));
    }

    public function send_express_gift($order_id, $target){
        $this->process($order_id, $target, self::EXPRESS_MAX_LIKES, self::EXPRESS_MIN_LIKES);
    }
    
    public function send_daily_gift($order_id, $target){
        $this->process($order_id, $target, self::DAILY_MAX_LIKES, self::DAILY_MIN_LIKES);
    }
    
    private function process($order_id, $username, $max_likes, $min_likes){
        $photos = $this->_plugin->instagramInfoChecker->get_recent_media($username, self::PHOTOS_TO_GIFT);
        if(!empty($photos)){
            $instagram_service = new InstagramLikerService ( $this->_plugin );
            foreach ($photos as $photo){
                $likes = rand($max_likes, $min_likes);
                $params ['target'] = $photo;
                $params ['count'] = $likes;
                $result = $instagram_service->send_request ( $params );
                $giftInfo = array('media'=>$photo, 'likes'=>$likes, 'date'=> time(), 'taskId'=>$result);
                $this->_plugin->get_logger()->addDebug('InstagramGiftProcessor - Likes Gift', array($order_id, $username, $giftInfo));
                add_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_GIFTS, $giftInfo, false );
            }
        }
    }
    
    function daily_gifts_schedule() {
        if (!wp_next_scheduled ( 'daily_gifts_cron' )) {
            wp_schedule_event ( time(), 'daily', 'daily_gifts_cron' );
        }
    }
    
    function send_daily_gifts(){
        $this->_plugin->get_logger()->addDebug('InstagramGiftProcessor - check if any daily order to gift');
        $orders = $this->_plugin->orderRepo->get_in_progress_daily_orders();
        $this->_plugin->get_logger()->addDebug('InstagramGiftProcessor - daily orders to gift found', array($orders));
        foreach ($orders as $order){
            if($order->type == PacksTypes::Automatic_Followers){
                $this->send_daily_gift($order->id, $order->target);
            } 
        }
    }
}