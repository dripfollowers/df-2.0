<?php

namespace DripFollowers\Service;

use DripFollowers\Common\DripFollowersConstants;
use DripFollowers\Common\PacksTypes;
use DripFollowers\Common\ProgressStatus;
use DripFollowers\Common\Pack;
use DripFollowers\Service\Api\InstagramIgersDripFollowerService;
use DripFollowers\Service\Api\InstagramIgersDripLikerService;
use DripFollowers\Service\Api\InstagramIgersLikerService;
use DripFollowers\Service\Api\InstagramOtlLikerService;
use DripFollowers\Service\Api\InstagramIgersViewsService;
use DripFollowers\Service\Api\InstagramExpressFollowerService;
use DripFollowers\Service\Api\InstagramExpressIgersFollowerService;
use DripFollowers\Service\Api\InstagramLikerService;
use DripFollowers\Service\Api\InstagramOBJAutoLService;
use DripFollowers\Service\Api\InstagramOBJAutoLikeService;
use DripFollowers\Service\Api\InstagramOBJAutoFService;
use DripFollowers\Service\Api\InstagramOBJAutoFolloService;
use DripFollowers\Service\Api\InstagramOBJFollowersService;
use DripFollowers\Service\Api\InstagramOBJFService;
use DripFollowers\Service\Api\InstagramOBJLikerService;
use DripFollowers\Service\Api\InstagramOBJService;
use DripFollowers\Service\Api\InstagramOBJSplitLService;
use DripFollowers\Service\Api\InstagramOBJSplitLikerService;
use DripFollowers\Service\Api\InstagramOBJVService;
use DripFollowers\Service\Api\InstagramOBJViewsService;
use DripFollowers\Service\Notifier\InstagramIssueNotifier;
use DripFollowers\DripFollowers;

class InstagramServiceProcessor {
    private $_plugin;
    
    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
    }

    public function handle_ipn($order_id, Pack $pack, $extraInfo) {
        $this->_plugin->get_logger ()->addDebug ( 'InstagramServiceProcessor handle_ipn', array ($order_id, $pack, $extraInfo ) );
        $instagram_service = null;
        if ($pack->get_type() == PacksTypes::Instant_Followers) {
            $instagram_service = new InstagramOBJFollowersService ( $this->_plugin );
            update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROVIDER, 'obj' );
        } else if ($pack->get_type() == PacksTypes::Instant_Likes) {
            $instagram_service = new InstagramOBJLikerService ( $this->_plugin ,'0');
			$this->_plugin->get_logger ()->addDebug ( 'InstagramServiceProcessor handle_ipn', array ($order_id, $pack, $extraInfo ) );
            update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROVIDER, 'obj' );
        } else if ($pack->get_type() == PacksTypes::Automatic_Followers) {
            $instagram_service = new InstagramOBJAutoFolloService ( $this->_plugin, $pack->get_drip_delay () );
            update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROVIDER, 'obj' );
        } else if ($pack->get_type() == PacksTypes::Instant_Views) {
            $instagram_service = new InstagramOBJLikerService ( $this->_plugin,'1' );
            update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROVIDER, 'obj' );
        } else if ($pack->get_type() == PacksTypes::Automatic_Likes) {
            $instagram_service = new InstagramOBJAutoLikeService ( $this->_plugin );
            update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROVIDER, 'obj' );
        } else if ($pack->get_type() == PacksTypes::Split_Likes) {
            $instagram_service = new InstagramOBJSplitLikerService( $this->_plugin );
            update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROVIDER, 'obj' );
        }
        $this->_plugin->get_logger ()->addDebug ( 'InstagramServiceProcessor to use', array ($instagram_service) );
        
        $params ['target'] = $extraInfo ['target'];
        $params ['count'] = $pack->get_number ();
        
        $result = $instagram_service->send_request ( $params );
        $this->_plugin->get_logger ()->addDebug ( 'InstagramServiceProcessor - send_request result', array ($order_id, $result));
        
        if ($result) {
            update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROGRESS, ProgressStatus::IN_PROGRESS );
            update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_TASK_ID, $result );
            update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_REMARKS, '' );
        } else {
            update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROGRESS, ProgressStatus::RE_SCHEDULED );
            update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_REMARKS, 'API server down or network error, an auto retry is scheduled' );
            $was_notified = get_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_ISSUE_NOTIFIED, true )==true;
            if(!$was_notified){
                $pack = $this->_plugin->packRepo->get_pack_by_order($order_id);
                $extraInfo = $this->_plugin->orderRepo->get_extra_info_by_order($order_id);
                $this->_plugin->get_logger()->addDebug('InstagramServiceProcessor - Notifying customer of a delay issue ', array($pack, $extraInfo));
                $issueNotifier = new InstagramIssueNotifier($this->_plugin, $pack, $extraInfo);
                $issueNotifier->notify();
                update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_ISSUE_NOTIFIED, true );
            }
        }
        
        @$this->_plugin->orderRepo->save_order_current_count($order_id, $extraInfo ['target'], $pack->get_type (), DripFollowersConstants::ORDER_COUNT_INITIAL_STAGE );
        
    }
}