<?php

namespace DripFollowers\Service\Api;

class InstagramOBJLikerService extends InstagramOBJService {

    private $_t;

    public function __construct($plugin, $t) {
        parent::__construct ( $plugin );
	$this->_t = $t;
    }

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
if(strcmp($this->_t, '1') == 0){
	$this->_data = 'api/views/' .$shortcode.'/'.$count;
}else{
	$this->_data = 'api/likes/' .$shortcode.'/'.$count;
}
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