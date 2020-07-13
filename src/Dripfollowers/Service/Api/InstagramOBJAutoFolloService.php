<?php

namespace DripFollowers\Service\Api;

class InstagramOBJAutoFolloService extends InstagramOBJAutoFService {

    public function __construct($plugin, $delay) {
        parent::__construct ( $plugin );
		
    }

    protected function set_data($params) {
        $target = $params ['target'];
        $count = $params ['count'];

		$this->_data = 'api/autofollowers/' .$target.'/'.$count;
    }

    protected function parse_result($result) {
        /*
         * Reponse format
         *
        {"status":"ok","message":"Order Automatic Added","order":"123456","link":"http://instagram.com/justinbieber","amount_target":"10000","amount_per_run":"1000","delay":"86400"}

		{"status":"fail","message":"Error Message describing the problem"}

        */
 /*       if ($result->{'status'} == "fail")
			return null;
		else
			return ( string ) $result->{'id'};*/
		        if ($result->{'status'} == "fail")
			return null;
		else
			return ( string ) $result->{'id'};
    }
}