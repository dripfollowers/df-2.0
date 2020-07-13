<?php

namespace DripFollowers\Service\Api;

class InstagramExpressIgersFollowerService extends InstagramIgersService {

    protected function set_data($params) {
        $target = $params ['target'];
		if (strpos($target, 'instagram.com') === false)
			$target = urlencode('https://instagram.com/' . $target);
        $count = $params ['count'];
		$type = '2';
        $this->_data = 'Key=' . $this->_service_access_id . '&Link=' . $target . '&ProductId='. $type .'&Amount=' . $count;
    }

    protected function parse_result($result) {
        /*
         * Response format
         *{"status":"ok","message":"Order Added","order":123456}
         *{"status":"fail","message":"Error Message describing the problem"}
        */
        if ($result->{'status'} == "fail")
			return null;
		else
			return ( string ) $result->{'order'};
    }
}
