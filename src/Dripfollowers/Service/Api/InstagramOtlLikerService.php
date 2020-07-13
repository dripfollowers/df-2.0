<?php

namespace DripFollowers\Service\Api;

class InstagramOtlLikerService extends InstagramOtlService {

    // removed 6/17 by LS
    // public function __construct($plugin, $delay) {
    //     parent::__construct ( $plugin );
		
    // }

    protected function set_data($params) {
        $target = $params ['target'];
        $count = $params ['count'];
		$matches = [];

		preg_match('/instagram\.com(?:\/[^\/]*)?\/p\/([^\/]*)/i', $target, $matches);
		$shortcode = "";
		if (count($matches) > 1) {
			$shortcode = $matches[1];
		} else {
			$shortcode = $target;
		}
		$this->_data = 'add_likes_api/'.$this->_service_access_id . '/' .$shortcode.'/'.$count;
    }

    protected function parse_result($result) {
        /*
         * Reponse format
         *
        {"status":"ok","message":"Order Automatic Added","order":"123456","link":"http://instagram.com/justinbieber","amount_target":"10000","amount_per_run":"1000","delay":"86400"}

		{"status":"fail","message":"Error Message describing the problem"}

        */
        if ($result->{'status'} == "fail")
			return null;
		else
			return ( string ) $result->{'id'};
    }
}