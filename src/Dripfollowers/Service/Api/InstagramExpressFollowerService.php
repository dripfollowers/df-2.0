<?php

namespace DripFollowers\Service\Api;

class InstagramExpressFollowerService extends InstagramService {

    protected function set_data($params) {
        $target = $params ['target'];
        $count = $params ['count'];
        $this->_data = '<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"
                        	xmlns:a="http://www.w3.org/2005/08/addressing">
                        	<s:Header>
                        		<a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/FollowUser
                        		</a:Action>
                        		<a:MessageID>urn:' . uniqid () . '</a:MessageID>
                        		<a:ReplyTo>
                        			<a:Address>http://www.w3.org/2005/08/addressing/anonymous</a:Address>
                        		</a:ReplyTo>
                        		<a:To s:mustUnderstand="1">' . $this->_service_url . '</a:To>
                        	</s:Header>
                        	<s:Body>
                        		<FollowUser xmlns="http://tempuri.org/">
                        			<accessId>' . $this->_service_access_id . '</accessId>
                        			<userName>' . $target . '</userName>
                        			<count>' . $count . '</count>
                        		</FollowUser>
                        	</s:Body>
                        </s:Envelope>';
    }

    protected function parse_result($result) {
        /*
         * Response format
         *
        <s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"
        	xmlns:a="http://www.w3.org/2005/08/addressing">
        	<s:Header>
        		<a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/FollowUserResponse
        		</a:Action>
        		<a:RelatesTo>urn:uuid:531b02d0d360e</a:RelatesTo>
        	</s:Header>
        	<s:Body>
        		<FollowUserResponse xmlns="http://tempuri.org/">
        			<FollowUserResult>729630</FollowUserResult>
        		</FollowUserResponse>
        	</s:Body>
        </s:Envelope>
        */
        
        return ( string ) $result->children ( 's', true )->Body->children ( 'http://tempuri.org/' )->FollowUserResponse->FollowUserResult;
    }
}