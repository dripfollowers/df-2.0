<?php

namespace DripFollowers\Service\Api;

class InstagramLikerService extends InstagramService {

    protected function set_data($params) {
        $target = $params ['target'];
        $count = $params ['count'];
        
        $this->_data = '<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"
                        	xmlns:a="http://www.w3.org/2005/08/addressing">
                        	<s:Header>
                        		<a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/LikeImage
                        		</a:Action>
                        		<a:MessageID>urn:' . uniqid () . '</a:MessageID>
                        		<a:ReplyTo>
                        			<a:Address>http://www.w3.org/2005/08/addressing/anonymous</a:Address>
                        		</a:ReplyTo>
                        		<a:To s:mustUnderstand="1">' . $this->_service_url . '</a:To>
                        	</s:Header>
                        	<s:Body>
                        		<LikeImage xmlns="http://tempuri.org/">
                        			<accessId>' . $this->_service_access_id . '</accessId>
                        			<image>' . $target . '</image>
                        			<count>' . $count . '</count>
                        		</LikeImage>
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
        		<a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/LikeImageResponse
        		</a:Action>
        		<a:RelatesTo>urn:uuid:5319924422038</a:RelatesTo>
        	</s:Header>
        	<s:Body>
        		<LikeImageResponse xmlns="http://tempuri.org/">
        			<LikeImageResult>723250</LikeImageResult>
        		</LikeImageResponse>
        	</s:Body>
        </s:Envelope>
         */
        return ( string ) $result->children ( 's', true )->Body->children ( 'http://tempuri.org/' )->LikeImageResponse->LikeImageResult;
    }
}