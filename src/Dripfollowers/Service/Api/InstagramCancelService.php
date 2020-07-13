<?php

namespace DripFollowers\Service\Api;

class InstagramCancelService extends InstagramService {

    protected function set_data($params) {
        $task_id = $params ['task_id'];
        
        $this->_data = '<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"
                    	xmlns:a="http://www.w3.org/2005/08/addressing">
                    	<s:Header>
                    		<a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/CancelTask
                    		</a:Action>
                    		<a:MessageID>urn:uuid:' . uniqid () . '</a:MessageID>
                    		<a:ReplyTo>
                    			<a:Address>http://www.w3.org/2005/08/addressing/anonymous</a:Address>
                    		</a:ReplyTo>
                    		<a:To s:mustUnderstand="1">' . $this->_service_url . '</a:To>
                    	</s:Header>
                    	<s:Body>
                    		<CancelTask xmlns="http://tempuri.org/">
                    			<accessId>' . $this->_service_access_id . '</accessId>
                    			<taskId>' . $task_id . '</taskId>
                    		</CancelTask>
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
        		<a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/CancelTaskResponse
        		</a:Action>
        		<a:RelatesTo>urn:uuid:4ec62994-f3eb-4682-b51c-41de98dca1f2</a:RelatesTo>
        	</s:Header>
        	<s:Body>
        		<CancelTaskResponse xmlns="http://tempuri.org/" />
        	</s:Body>
        </s:Envelope>
         */
        return isset($result->children ( 's', true )->Body->children ( 'http://tempuri.org/' )->CancelTaskResponse);
    }
}