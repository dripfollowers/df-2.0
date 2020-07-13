<?php

namespace DripFollowers\Service\Api;

class InstagramStatusCheckerIgersService extends InstagramIgersService {

    protected function set_data($params) {
        // $this->_plugin->get_logger()->addDebug('InstagramStatusCheckerService - set_data with params', array($params));
        $orders = $params ['orders'];
		
        if (is_array ( $orders ) && ! empty ( $orders )) {
			$task_ids = array();
			$i = 0;
			foreach ( $orders as $order ) {
				if ($i++ > 197) break;
				array_push($task_ids, $order->task_id);
			}
            $this->_data = 'key=' . $this->_service_access_id . '&action=order_status_multiple&order_id=' . urlencode(implode(',', $task_ids));
        }
        
        // $this->_plugin->get_logger()->addDebug('InstagramStatusCheckerService - set_data to send', array($this->_data));
    }

    protected function parse_result($json) {
        /*
         * Reponse format
         * 
         {"status":"ok","count_submitted":2,"count_failed":0,"count_items":2,"items":[{"status":"ok","type":"ig_followers_fast","time":"1472759062","amount":"20","price":"0.0240000","link":"https:\/\/www.instagram.com\/chrisworldmusic\/","order_status":"Completed","order_status_error":"This order is completed! Thank you.","count_start":"9543","count_current":"9563","count_remain":"0","count_finish":"9563","order_id":"3570843"},{"status":"ok","type":"ig_followers_fast","time":"1472759034","amount":"20","price":"0.0240000","link":"https:\/\/www.instagram.com\/tandamodern\/","order_status":"Completed","order_status_error":"This order is completed! Thank you.","count_start":"9755","count_current":"9775","count_remain":"0","count_finish":"9775","order_id":"3570841"}]}
		 
		{"status":"fail","message":"Error Message describing the problem"}
 
         */
		
        $items = $json->{'items'};
		$result = array ();
        foreach ( $items as $item ) {
            $task = new \stdClass ();
            $task->task_id = $item->{'order_id'};
            $task->is_completed = (( $item->{'order_status'}) == "Completed") ? true : false;
            $task->not_found = (($item->{'order_status'}) == "Refunded") ? true : false;
            $task->initial_count = $item->{'count_start'};
            $result [$task->task_id] = $task;
        }
        return $result;
    }
}