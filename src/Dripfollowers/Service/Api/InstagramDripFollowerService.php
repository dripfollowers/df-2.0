<?php

namespace DripFollowers\Service\Api;

class InstagramDripFollowerService extends InstagramService {
    private $_delay;

    public function __construct($plugin, $delay) {
        parent::__construct ( $plugin );
        $this->_delay = $delay;
    }

    protected function set_data($params) {
        $target = $params ['target'];
        $count = $params ['count'];
        $this->_data = '<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"
                        	xmlns:a="http://www.w3.org/2005/08/addressing">
                        	<s:Header>
                        		<a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/SlowFollowUser
                        		</a:Action>
                        		<a:MessageID>urn:' . uniqid () . '</a:MessageID>
                        		<a:ReplyTo>
                        			<a:Address>http://www.w3.org/2005/08/addressing/anonymous</a:Address>
                        		</a:ReplyTo>
                        		<a:To s:mustUnderstand="1">' . $this->_service_url . '</a:To>
                        	</s:Header>
                        	<s:Body>
                        		<SlowFollowUser xmlns="http://tempuri.org/">
                        			<accessId>' . $this->_service_access_id . '</accessId>
                        			<userName>' . $target . '</userName>
                        			<count>' . $count . '</count>
                        			<minDelay>' . $this->_delay . '</minDelay>
                        			<maxDelay>' . $this->_delay . '</maxDelay>
                        			<startTimeString xmlns:i="http://www.w3.org/2001/XMLSchema-instance" i:nil="true" />
                        		</SlowFollowUser>
                        	</s:Body>
                        </s:Envelope>';
    }

    protected function parse_result($result) {
        /*
         * Reponse format
         *
        <s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"
        	xmlns:a="http://www.w3.org/2005/08/addressing">
        	<s:Header>
        		<a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/SlowFollowUserResponse
        		</a:Action>
        		<a:RelatesTo>urn:uuid:531b0de8e5360</a:RelatesTo>
        	</s:Header>
        	<s:Body>
        		<SlowFollowUserResponse xmlns="http://tempuri.org/">
        			<SlowFollowUserResult>729771</SlowFollowUserResult>
        		</SlowFollowUserResponse>
        	</s:Body>
        </s:Envelope>
        */
        return ( string ) $result->children ( 's', true )->Body->children ( 'http://tempuri.org/' )->SlowFollowUserResponse->SlowFollowUserResult;
    }
}