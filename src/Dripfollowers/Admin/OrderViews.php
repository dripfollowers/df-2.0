<?php

namespace DripFollowers\Admin;

use DripFollowers\DripFollowers;

use DripFollowers\Common\DripFollowersConstants;
use DripFollowers\Common\PacksTypes;
use DripFollowers\Common\Pack;
use DripFollowers\Common\ProgressStatus;
use DripFollowers\Service\Notifier\InstagramFinishNotifier;
use DripFollowers\Service\Notifier\InstagramStartNotifier;
use DripFollowers\Service\Api\InstagramCancelService;
use DripFollowers\Service\Api\InstagramIgersCancelService;
use DripFollowers\Service\Api\InstagramIgersCancelAutoService;
use DripFollowers\Service\Api\InstagramResumeService;
use DripFollowers\Service\Api\InstagramIgersDripFollowerService;
use DripFollowers\Service\Api\InstagramIgersService;
use DripFollowers\Service\Api\InstagramExpressIgersFollowerService;
use DripFollowers\Service\Api\InstagramIgersLikerService;
use DripFollowers\Service\Api\InstagramOtlLikerService;
use DripFollowers\Service\Api\InstagramOtlViewsService;
use DripFollowers\Service\Api\InstagramDripFollowerService;
use DripFollowers\Service\Api\InstagramExpressFollowerService;
use DripFollowers\Service\Api\InstagramLikerService;

class OrderViews {
    const MARK_AS_DONE     = 'mark_as_done';
	const MARK_AS_UNDONE   = 'mark_as_undone';
	const NOTICE_OF_START  = 'notice_of_start';
	const MARK_AS_VALID    = 'mark_as_valid';
	const TASK_CANCEL      = 'task_cancel';
	const TASK_RESUME      = 'task_resume';
	const TRANSFER_IGERS   = 'transfer_igers';
	const TRANSFER_OTL   = 'transfer_otl';
	const TRANSFER_IGERS_MEDIUM   = 'transfer_igers_medium';
	
    private $_plugin;

    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
        
        add_action ( 'init', array (&$this,'register_cpt_driporder') );
        add_action ( 'admin_menu', array (&$this,'add_admin_menu') );
        add_filter ( 'manage_edit-driporder_columns', array (&$this,'set_custom_edit_driporder_columns') );
        add_action ( 'manage_driporder_posts_custom_column', array (&$this,'custom_driporder_column'), 10, 2 );
        // add_filter( 'bulk_actions-edit-driporder', '__return_empty_array' );
        add_action ( 'wp_ajax_do_for_orders', array (&$this,'do_for_orders_callback') );
        add_action ( 'wp_ajax_export_orders', array (&$this,'export_orders_callback') );
    }
    
    public function add_admin_menu() {
        $main_page = 'edit.php?post_type=' . DripFollowersConstants::CPT_DRIP_ORDER;
        //add_menu_page ( 'Drip Followers Orders', 'Orders', 'manage_options', $main_page, false, false, 3 );
        add_action ( 'admin_print_styles-edit.php?post-type=driporder&page=drip_settings', array (&$this,'enqueue_admin_style' ) );
        add_action ( 'admin_print_scripts-edit.php', array (&$this,'enqueue_admin_script') );
    }
    
    function enqueue_admin_style() {
        wp_enqueue_style ( 'drip-followers-admin.css', $this->_plugin->get_plugin_url () . 'css/admin_style.css', array (), '1.0' );
    }
    
    function enqueue_admin_script() {
        global $post_type;
        if ('driporder' == $post_type) {
            wp_enqueue_script ( 'drip-followers-admin.js', $this->_plugin->get_plugin_url () . 'js/admin_script.js', array ('jquery'), '2.0' );
        }
    }

    function do_for_orders_callback() {
        $choice = $_POST ['choice'];
        $orders = $_POST ['orders'];
        $this->_plugin->get_logger ()->addDebug ( 'OrderViews - ajax callback', array ($choice, $orders) );
        header('HTTP/1.1 200 OK');
        if ($choice == self::MARK_AS_DONE || $choice == self::MARK_AS_UNDONE) {
            echo $this->update_orders_progress ( $choice, $orders );
        } elseif ($choice == self::TASK_CANCEL || $choice == self::TASK_RESUME) {
            echo $this->cancel_or_resume_orders ( $choice, $orders );
        } elseif($choice == self::NOTICE_OF_START){
            echo $this->notice_of_start($orders);
        } elseif($choice == self::MARK_AS_VALID){
            echo $this->mark_as_valid($orders);
        } elseif ($choice == self::TRANSFER_IGERS) {
			echo $this->transfer_to_igers($orders);
        
        } elseif ($choice == self::TRANSFER_OTL) {
			echo $this->transfer_to_otl($orders);
        }
        die ();
    }
    
	function transfer_to_otl($orders) {
		
		foreach ($orders as $order_id) {			
			$pack = $this->_plugin->packRepo->get_pack_by_order($order_id);
			$extraInfo = $this->_plugin->orderRepo->get_extra_info_by_order($order_id);
			$service = get_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_SERVICE, true );
			
			$provider = get_post_meta($order_id, DripFollowersConstants::COL_ORDER_PROVIDER, true);
			
			// ignore igers
            if (!empty($provider) && $provider == 'igers') {				
				// cancel arsen order
				$task_id = get_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_TASK_ID, true );
				if (!empty($task_id) && $task_id != '') {
					$cancel = new InstagramIgersCancelService($this->_plugin);
					$result = $cancel->send_request(array('task_id' => $task_id, 'order_id'=>$order_id));
					if($result) 
						update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROGRESS, self::TASK_CANCEL );
				}
			}
			
			$instagram_service = null;
			if ($service == 'instant-likes') 
				$instagram_service = new InstagramOtlLikerService ( $this->_plugin );
			else if ($service == 'views')
				$instagram_service = new InstagramOtlViewsService ( $this->_plugin );
			
			$params ['target'] = $extraInfo ['target'];
			$params ['count'] = $pack->get_number ();        
			$result = $instagram_service->send_request ( $params );
			if ($result) {
				update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROGRESS, ProgressStatus::IN_PROGRESS );
				update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_TASK_ID, $result );
				update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_REMARKS, 'Transferred (restarts)' );
				update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROVIDER, 'otl' );
				$success [] = $order_id;
			} 
            else {
                $errors [] = $order;
            }
        }
		
        return '{"success": ' . json_encode ( $success ) . ', "errors": ' . json_encode ( $errors ) . '}';		
		
	}
	
	function transfer_to_igers($orders) {
		
		foreach ($orders as $order_id) {			
			$pack = $this->_plugin->packRepo->get_pack_by_order($order_id);
			$extraInfo = $this->_plugin->orderRepo->get_extra_info_by_order($order_id);
			$service = get_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_SERVICE, true );
			
			$provider = get_post_meta($order_id, DripFollowersConstants::COL_ORDER_PROVIDER, true);
			
			// ignore igers
            if (!empty($provider) && $provider == 'arsen') {				
				// cancel arsen order
				$task_id = get_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_TASK_ID, true );
				if (!empty($task_id) && $task_id != '') {				
					$cancel = new InstagramCancelService($this->_plugin);
					$result = $cancel->send_request(array('task_id' => $task_id, 'order_id'=>$order_id));
					if($result) 
						update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROGRESS, self::TASK_CANCEL );
				}
			}
			
			$instagram_service = null;
			if ($service == 'instant-followers') {
				// send to igers
				$instagram_service = new InstagramExpressIgersFollowerService ( $this->_plugin );
			}
			else if ($service == 'automatic-followers') {
				$instagram_service = new InstagramIgersDripFollowerService ( $this->_plugin, $pack->get_drip_delay () );				
			}
			else if ($service == 'instant-likes') {
				$instagram_service = new InstagramIgersLikerService ( $this->_plugin );
				
			}
			$params ['target'] = $extraInfo ['target'];
			$params ['count'] = $pack->get_number ();        
			$result = $instagram_service->send_request ( $params );
			if ($result) {
				update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROGRESS, ProgressStatus::IN_PROGRESS );
				update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_TASK_ID, $result );
				update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_REMARKS, 'Transferred (restarts)' );
				update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROVIDER, 'igers' );
				$success [] = $order_id;
			} 
            else {
                $errors [] = $order;
            }
        }
		
        return '{"success": ' . json_encode ( $success ) . ', "errors": ' . json_encode ( $errors ) . '}';		
		
	}
	
    function cancel_or_resume_orders($choice, $orders) {
        $insta_service = null;
		$igers_service = null;
		$igers_autoservice = null;
        $progress = null;
        if ($choice == self::TASK_CANCEL) {
            $progress = ProgressStatus::CANCELED;
            $insta_service = new  InstagramCancelService($this->_plugin);
            $igers_service = new  InstagramIgersCancelService($this->_plugin);
            $igers_autoservice = new  InstagramIgersCancelAutoService($this->_plugin);
        } elseif ($choice == self::TASK_RESUME){
            $progress = ProgressStatus::IN_PROGRESS;
            $insta_service = new InstagramResumeService($this->_plugin);
        }
        
        if(isset($insta_service) && isset($progress)){
            foreach ( $orders as $order ) {
				
                $task_id = get_post_meta ( $order, DripFollowersConstants::COL_ORDER_TASK_ID, true );
                $service = get_post_meta ( $order, DripFollowersConstants::COL_ORDER_SERVICE, true );
                $provider = get_post_meta ( $order, DripFollowersConstants::COL_ORDER_PROVIDER, true );
				if (empty($provider)) $provider = 'test';
                if($task_id != ''){
                    if ($choice == self::TASK_CANCEL)
						delete_post_meta ( $order, DripFollowersConstants::COL_ORDER_TASK_ID );
					$result = null;
					if ($choice == self::TASK_CANCEL && $provider == 'igers' && $service == 'automatic-followers')
						$result = $igers_autoservice->send_request(array('task_id' => $task_id, 'order_id'=>$order));
					else if ($choice == self::TASK_CANCEL && $provider == 'igers' && $service == 'instant-followers')
						$result = $igers_service->send_request(array('task_id' => $task_id, 'order_id'=>$order));
					else if ($provider != 'igers')
						$result = $insta_service->send_request(array('task_id' => $task_id, 'order_id'=>$order));
                    $this->_plugin->get_logger()->addDebug('OrderViews - cancel or resume result/order/task/progress',array($result, $order, $task_id, $progress ));
                    if($result){
                        if ($choice == self::TASK_CANCEL) {
                            update_post_meta ( $order, DripFollowersConstants::COL_ORDER_PROGRESS, $progress );
                        }
                        $success [] = $order;
                    } else {
                        $errors [] = $order;
                    }
                } else if ($choice == self::TASK_RESUME) { 
					$instagram_service = null;
					
					$pack = $this->_plugin->packRepo->get_pack_by_order ( $order );
					
					if ($pack->get_type () == PacksTypes::Instant_Followers) {
						$instagram_service = new InstagramExpressFollowerService ( $this->_plugin );
					} elseif ($pack->get_type () == PacksTypes::Instant_Likes) {
						$instagram_service = new InstagramLikerService ( $this->_plugin );
					} elseif ($pack->get_type () == PacksTypes::Automatic_Followers) {
						$instagram_service = new InstagramDripFollowerService ( $this->_plugin, $pack->get_drip_delay () );
					}
					
					$this->_plugin->get_logger ()->addDebug ( 'InstagramServiceProcessor to use', array ($instagram_service) );
					
					$params ['target'] = get_post_meta ( $order, DripFollowersConstants::COL_ORDER_TARGET, true);
					$params ['count'] = get_post_meta ( $order, DripFollowersConstants::COL_ORDER_NUMBER, true);
					
					$result = $instagram_service->send_request ( $params );
					
                    $this->_plugin->get_logger()->addDebug('OrderViews - cancel or resume result/order/task/progress',array($result, $order, $result, $progress ));
                    if($result){
						update_post_meta ( $order, DripFollowersConstants::COL_ORDER_PROGRESS, ProgressStatus::IN_PROGRESS );
						update_post_meta ( $order, DripFollowersConstants::COL_ORDER_TASK_ID, $result );
						update_post_meta ( $order, DripFollowersConstants::COL_ORDER_PROVIDER, 'test' );
						update_post_meta ( $order, DripFollowersConstants::COL_ORDER_REMARKS, '' );
                        $success [] = $order;
                    } else {
						update_post_meta ( $order, DripFollowersConstants::COL_ORDER_PROGRESS, ProgressStatus::RE_SCHEDULED );
						update_post_meta ( $order, DripFollowersConstants::COL_ORDER_REMARKS, 'Failed to send' );
                        $errors [] = $order;
                    }
				}
            }
        }
        return '{"success": ' . json_encode ( $success ) . ', "errors": ' . json_encode ( $errors ) . '}';
    }
    
    
    function export_orders_callback(){
        $m = $_REQUEST ['m'];
        $this->_plugin->get_logger ()->addDebug ( 'OrderViews - export_orders_callback', array($m));
        $orders = $this->_plugin->orderRepo->get_orders_for_export(array('m'=>$m));
        header('Content-type: application/vnd.ms-excel');
        header('Content-disposition: attachment; filename="orders.csv"');
        $excel_delimiter = ';';
        $out = fopen('php://output', 'w');
        
        $columns = array(
                "Date","Customer","Contact Email","Service",
                "Pack","Upsell","Number","Target","Initial Count", "Final Count",
                "Payment Status","Payment Amount",
                "TRX ID","Progress","Remarks"
        );
        fputcsv($out, $columns, $excel_delimiter);
        
        foreach ($orders as $order){
            $values = array(
                    $order->date,$order->customer,$order->contact_email,$order->service,
                    $order->pack,$order->with_upsell,$order->number,$order->target,$order->initial_count,$order->final_count,
                    $order->payment_status,$order->payment_amount,
                    $order->trx_id,$order->progress,$order->remarks
            );
            fputcsv($out, $values, $excel_delimiter);
        }
        fclose($out);
        die();
    }

    function update_orders_progress($choice, $orders) {
        $progress = '-';
        if ($choice == self::MARK_AS_DONE) {
            $progress = ProgressStatus::DONE;
        }
        foreach ( $orders as $order ) {
            if (update_post_meta ( $order, DripFollowersConstants::COL_ORDER_PROGRESS, $progress )) {
                if($progress == ProgressStatus::DONE){
                    update_post_meta ( $order, DripFollowersConstants::COL_ORDER_REMARKS, '' );
                    $this->notice_of_completion(array($order));
                }
                $success [] = $order;
            } else {
                $errors [] = $order;
            }
        }
        return '{"success": ' . json_encode ( $success ) . ', "errors": ' . json_encode ( $errors ) . '}';
    }
    
    function mark_as_valid($orders) {
        foreach ( $orders as $order ) {
            if (update_post_meta ( $order, DripFollowersConstants::COL_ORDER_PAYMENT_STATUS, DripFollowersConstants::STATUS_PAYMENT_VALID )) {
                update_post_meta ( $order, DripFollowersConstants::COL_ORDER_PROGRESS, ProgressStatus::RE_SCHEDULED );
                update_post_meta ( $order, DripFollowersConstants::COL_ORDER_REMARKS, '' );
                $success [] = $order;
            } else {
                $errors [] = $order;
            }
        }
        return '{"success": ' . json_encode ( $success ) . ', "errors": ' . json_encode ( $errors ) . '}';
    }
    
    private function notice_of_start($orders){
        foreach ($orders as $orderId){
            $extraInfo = $this->_plugin->orderRepo->get_extra_info_by_order($orderId);
            $pack = $this->_plugin->packRepo->get_pack_by_order($orderId);
            $startNotifier = new InstagramStartNotifier($this->_plugin, $pack, $extraInfo);
            if($startNotifier->notify()){
                $success [] = $orderId;
            } else {
                $errors [] = $orderId;
            }
        }
        return '{"success": ' . json_encode ( $success ) . ', "errors": ' . json_encode ( $errors ) . '}';
    }
    
    private function notice_of_completion($orders){
        foreach ($orders as $orderId){
            $extraInfo = $this->_plugin->orderRepo->get_extra_info_by_order($orderId);
            $pack = $this->_plugin->packRepo->get_pack_by_order($orderId);
            $finishNotifier = new InstagramFinishNotifier($this->_plugin, $pack, $extraInfo);
            if($finishNotifier->notify()){
                $success [] = $orderId;
            } else {
                $errors [] = $orderId;
            }
        }
        return '{"success": ' . json_encode ( $success ) . ', "errors": ' . json_encode ( $errors ) . '}';
    }

    function register_cpt_driporder() {
        register_post_type ( DripFollowersConstants::CPT_DRIP_ORDER, 
        array (
            'label' => 'Drip Followers Orders',
            'menu_icon' => 'dashicons-cart',
            'menu_position' => 3,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'capability_type' => 'post',
            'capabilities' => array (
                        'create_posts' => false 
            ),
            'map_meta_cap' => true,
            'hierarchical' => false,
            'rewrite' => array (
                'slug' => 'driporder',
                'with_front' => true 
            ),
            'query_var' => false,
            'exclude_from_search' => true,
            'supports' => array (
                'custom-fields' 
            ),
            'labels' => array (
                'name' => 'Drip Followers Orders',
                'singular_name' => 'Drip Followers Order',
                'menu_name' => 'Drip Followers Orders',
                'view' => 'View Drip Followers Orders',
                'view_item' => 'View Drip Followers Orders',
                'search_items' => 'Search Drip Followers Orders',
                'not_found' => 'No Drip Followers Orders Found',
                'not_found_in_trash' => 'No Drip Followers Orders Found in Trash' 
            ) 
        ) );
    }

    function set_custom_edit_driporder_columns($columns) {
        // unset ( $columns ['cb'] );
        unset ( $columns ['title'] );
        unset ( $columns ['date'] );
        $columns [DripFollowersConstants::COL_ORDER_CUSTOMER] = __ ( 'Customer' );
        $columns [DripFollowersConstants::COL_ORDER_CONTACT_EMAIL] = __ ( 'Contact Email' );
        $columns [DripFollowersConstants::COL_ORDER_SERVICE] = __ ( 'Service' );
        $columns [DripFollowersConstants::COL_ORDER_PACK] = __ ( 'Pack' );
        $columns [DripFollowersConstants::COL_ORDER_WITH_UPSELL] = __ ( 'Upsell' );
        $columns [DripFollowersConstants::COL_ORDER_NUMBER] = __ ( 'Number' );
        $columns [DripFollowersConstants::COL_ORDER_INITIAL_COUNT] = __ ( 'Init. Count' );
        $columns [DripFollowersConstants::COL_ORDER_FINAL_COUNT] = __ ( 'Final Count' );
        $columns [DripFollowersConstants::COL_ORDER_PROGRESS] = __ ( 'Progress' );
        $columns [DripFollowersConstants::COL_ORDER_TARGET] = __ ( 'Target' );
        $columns [DripFollowersConstants::COL_ORDER_PROVIDER] = __ ( 'Provider' );
        $columns [DripFollowersConstants::COL_ORDER_PAYMENT_STATUS] = __ ( 'Payment Status' );
        $columns [DripFollowersConstants::COL_ORDER_PAYMENT_AMOUNT] = __ ( 'Payment Amount', 'drip-followers' );
        $columns [DripFollowersConstants::COL_ORDER_PAYMENT_TRX_ID] = __ ( 'TRX ID', 'drip-followers' );
        $columns [DripFollowersConstants::COL_ORDER_TASK_ID] = __ ( 'API Task ID', 'drip-followers' );
        $columns [DripFollowersConstants::COL_ORDER_DATE] = __ ( 'Date' );
        $columns [DripFollowersConstants::COL_ORDER_REMARKS] = __ ( 'Remarks' );
        return $columns;
    }

    public function custom_driporder_column($column, $post_id) {
        $service = get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_SERVICE, true );
        
        switch ($column) {
            case DripFollowersConstants::COL_ORDER_CUSTOMER :
                echo get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_CUSTOMER, true );
                break;
            case DripFollowersConstants::COL_ORDER_CONTACT_EMAIL :
                echo get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_CONTACT_EMAIL, true );
                break;
            case DripFollowersConstants::COL_ORDER_SERVICE :
                echo $service;
                break;
            case DripFollowersConstants::COL_ORDER_PACK :
                echo get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_PACK, true );
                break;
            case DripFollowersConstants::COL_ORDER_WITH_UPSELL :
                echo (get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_WITH_UPSELL, true ) == true ? 'Yes' : 'No');
                break;
            case DripFollowersConstants::COL_ORDER_NUMBER :
                echo get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_NUMBER, true );
                break;
            case DripFollowersConstants::COL_ORDER_INITIAL_COUNT :
                echo get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_INITIAL_COUNT, true );
                break;
           case DripFollowersConstants::COL_ORDER_FINAL_COUNT :
               	echo get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_FINAL_COUNT, true );
               	break;
            case DripFollowersConstants::COL_ORDER_PROGRESS :
                echo get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_PROGRESS, true );
                break;
            case DripFollowersConstants::COL_ORDER_PROVIDER :
                $provider = get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_PROVIDER, true );
				if (empty($provider)) echo "test";
				else echo $provider;
                break;
            case DripFollowersConstants::COL_ORDER_TARGET :
                $target = get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_TARGET, true );
                if ($service == "likes"){
                    if(strpos($target, 'http://') === false && strpos($target, 'https://') === false) {
                        $target = 'http://'.$target;
                    }
                    echo "<a target='_blank' href='$target'>Media</a>";
                } else {
                    echo "<a target='_blank' href='http://instagram.com/$target'>Username</a>";
                }
                break;
            case DripFollowersConstants::COL_ORDER_PAYMENT_STATUS :
                echo get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_PAYMENT_STATUS, true );
                break;
            case DripFollowersConstants::COL_ORDER_PAYMENT_AMOUNT :
                echo get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_PAYMENT_AMOUNT, true );
                break;
            case DripFollowersConstants::COL_ORDER_PAYMENT_TRX_ID :
                echo get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_PAYMENT_TRX_ID, true );
                break;
            case DripFollowersConstants::COL_ORDER_TASK_ID :
                echo get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_TASK_ID, true );
                break;
            case DripFollowersConstants::COL_ORDER_DATE :
                $post = get_post ( $post_id );
                echo $post->post_date;
                break;
            case DripFollowersConstants::COL_ORDER_REMARKS :
                echo get_post_meta ( $post_id, DripFollowersConstants::COL_ORDER_REMARKS, true );
                break;
        }
    }
}
