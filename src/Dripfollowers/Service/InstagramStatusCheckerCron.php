<?php

namespace DripFollowers\Service;

use DripFollowers\DripFollowers;
use DripFollowers\Common\DripFollowersConstants;
use DripFollowers\Common\ProgressStatus;
use DripFollowers\Service\Api\InstagramStatusCheckerService;
use DripFollowers\Service\Api\InstagramStatusCheckerIgersService;
use DripFollowers\Service\Notifier\InstagramFinishNotifier;
use DripFollowers\Common\PacksTypes;

class InstagramStatusCheckerCron {
    private $_plugin;

    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
        
        add_action ( 'wp', array (&$this,'instagram_progress_checker_schedule' ) );
        add_action ( 'instagram_progress_cron', array (&$this,'check_executed_orders' ) );
    }

    public function check_executed_orders() {
        try {
            $this->_plugin->get_logger ()->addDebug ( 'InstagramStatusCheckerCron - Check executed orders status' );
            $orders = $this->_plugin->orderRepo->get_executed_orders ();
			
            if(is_array($orders) && !empty($orders) ){
				
				
                $this->_plugin->get_logger ()->addDebug ( 'InstagramStatusCheckerCron - Orders found ', array ($orders) );
                
				// split igers and arsen
								
				$arsen_orders = array();
				$igers_orders = array();
				$otl_orders = array();
				foreach ($orders as $order) {
					if ($order->provider == 'arsen')
						array_push($arsen_orders, $order);
					else if ($order->provider == 'igers')
						array_push($igers_orders, $order);
					else if ($order->provider == 'otl')
						array_push($otl_orders, $order);
				}
                $checker = new InstagramStatusCheckerService ( $this->_plugin );
                $params ['orders'] = $arsen_orders;
                $tasks = $checker->send_request ( $params );
				
                $checker_2 = new InstagramStatusCheckerIgersService ( $this->_plugin );
                $params ['orders'] = $igers_orders;
				$tasks_2 = $checker_2->send_request ( $params );
                $tasks = array_merge($tasks, $tasks_2);
				
                foreach ( $tasks as $task ) {
					try{
						$task_key = 'task_'.$task->task_id;
						$order = $orders [$task_key];
						if(!isset($order)){
							$msg = "Can't found In Progress order with the taskId ".$task->task_id;
							//throw \Exception($msg);
							$this->_plugin->get_logger ()->addError($msg);
						} else {
							if ($task->is_completed == true) {
								
								$this->_plugin->get_logger ()->addDebug ( 'InstagramStatusCheckerCron - Task completed found ', array ($task, $order ) );
								if (update_post_meta ( $order->id, DripFollowersConstants::COL_ORDER_PROGRESS, ProgressStatus::DONE )) {
									update_post_meta ( $order->id, DripFollowersConstants::COL_ORDER_REMARKS, '' );
									//
									$pack = $this->_plugin->packRepo->get_pack_by_order ( $order->id );
									
									$extraInfo ['email'] = $order->contact_email;
									$extraInfo ['paypal_email'] = $order->customer;
									$extraInfo ['target'] = $order->target;
									
									$finishNotifier = new InstagramFinishNotifier ( $this->_plugin, $pack, $extraInfo );
									$finishNotifier->notify ();
									
									if(PacksTypes::Instant_Followers == $order->type){
										if(isset($this->_plugin->giftProcessor)){
											$this->_plugin->giftProcessor->send_express_gift($order->id, $order->target);
										}
									}
									
									$this->_plugin->orderRepo->save_order_current_count($order->id, $extraInfo ['target'], $order->type, DripFollowersConstants::ORDER_COUNT_FINAL_STAGE);
								}
							} else if ($task->not_found) {
								update_post_meta ( $order->id, DripFollowersConstants::COL_ORDER_PROGRESS, ProgressStatus::CANCELED );
								update_post_meta ( $order->id, DripFollowersConstants::COL_ORDER_REMARKS, 'Refunded' );
								if (!empty($task->current_count))
									update_post_meta ( $order->id, DripFollowersConstants::COL_ORDER_FINAL_COUNT, $task->current_count );
							}
							
						}
					} catch (\Exception $e){
						$this->_plugin->get_logger()->addError("StatusCheckerCron - Error while running cron: ",array($e->getTraceAsString()));
					}
                }
            } else {
                $this->_plugin->get_logger ()->addDebug ( 'InstagramStatusCheckerCron - no orders found to check status' );
            }
        } catch (\Exception $e) {
            $this->_plugin->get_logger ()->addError($e->getMessage());
        }
    }

    function instagram_progress_checker_schedule() {
        if (! wp_next_scheduled ( 'instagram_progress_cron' )) {
            wp_schedule_event ( time (), '15minutely', 'instagram_progress_cron' );
            //wp_schedule_event ( time (), 'minutely', 'instagram_progress_cron' );
        }
    }
}