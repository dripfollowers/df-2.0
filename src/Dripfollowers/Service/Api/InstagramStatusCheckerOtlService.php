<?php

namespace DripFollowers\Service\Api;

class InstagramStatusCheckerOtlService extends InstagramOtlService {

    protected function set_data($params) {
        // $this->_plugin->get_logger()->addDebug('InstagramStatusCheckerService - set_data with params', array($params));
        $order = $params ['order'];
		
        $this->_data = 'order_status_api/' . $this->_service_access_id . '/' . $order->task_id;
        
        
        // $this->_plugin->get_logger()->addDebug('InstagramStatusCheckerService - set_data to send', array($this->_data));
    }

    protected function parse_result($json) {
        /*
         * Reponse format
         * 
         {"status":"ok","count_submitted":2,"count_failed":0,"count_items":2,"items":[{"status":"ok","type":"ig_followers_fast","time":"1472759062","amount":"20","price":"0.0240000","link":"https:\/\/www.instagram.com\/chrisworldmusic\/","order_status":"Completed","order_status_error":"This order is completed! Thank you.","count_start":"9543","count_current":"9563","count_remain":"0","count_finish":"9563","order_id":"3570843"},{"status":"ok","type":"ig_followers_fast","time":"1472759034","amount":"20","price":"0.0240000","link":"https:\/\/www.instagram.com\/tandamodern\/","order_status":"Completed","order_status_error":"This order is completed! Thank you.","count_start":"9755","count_current":"9775","count_remain":"0","count_finish":"9775","order_id":"3570841"}]}
		 
		{"status":"fail","message":"Error Message describing the problem"}
 
         */
		
        $item = $json->{'order_status'};
		$result = array ();
		$task = new \stdClass ();
		$task->task_id = $item->{'id'};
		$task->is_completed = $item->{'is_completed'});
		$task->not_found = $item->{'is_refunded'});
		$task->initial_count = $item->{'begin_likes'};
		$result [$task->task_id] = $task;
	
        return $result;
    }
}