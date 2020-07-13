<?php

namespace DripFollowers\Service\Api;

class InstagramIgersCancelService extends InstagramIgersService {

    protected function set_data($params) {
        $task_id = $params ['task_id'];
       /*

key - Your private key
action - order_stop
order_id - The id of the order to stop

*/	   
        $this->_data = 'key=' . $this->_service_access_id . '&action=order_stop&order_id=' . $task_id;
    }

    protected function parse_result($result) {
        /*
         * Reponse format
         *
		 {"status":"ok","message":"Your order is now removed","type":"ig_followers","time_added":"1438811454","time_updated":"1438811454","time_last_delivery":"1438811522","amount_limit":"1000","amount_per_order":"100","link":"http:\/\/instagram.com\/justinbieber","order_status":"Waiting Time","order_status_error":"We have placed the order! The script will now wait until next time to delivery.","order_ids_done":[1837867],"order_id":"123456"}

         */
        return $result->{'status'} == "ok";
    }
}