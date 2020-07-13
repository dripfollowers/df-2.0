<?php

namespace DripFollowers\Service\Api;

use DripFollowers\Common\ProgressStatus;
use DripFollowers\Common\DripFollowersConstants;

class InstagramResumeService extends InstagramService {
    
    public function send_request($params) {
        $order_id = $params['order_id'];
        return update_post_meta ( $order_id, DripFollowersConstants::COL_ORDER_PROGRESS, ProgressStatus::RE_SCHEDULED );
    }

    protected function set_data($params) {
        $task_id = $params ['task_id'];
        
        $this->_data = '<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"
                        	xmlns:a="http://www.w3.org/2005/08/addressing">
                        	<s:Header>
                        		<a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/ResumeTask
                        		</a:Action>
                        		<a:MessageID>urn:uuid:' . uniqid () . '</a:MessageID>
                        		<a:ReplyTo>
                        			<a:Address>http://www.w3.org/2005/08/addressing/anonymous</a:Address>
                        		</a:ReplyTo>
                        		<a:To s:mustUnderstand="1">' . $this->_service_url . '</a:To>
                        	</s:Header>
                        	<s:Body>
                        		<ResumeTask xmlns="http://tempuri.org/">
                        			<accessId>' . $this->_service_access_id . '</accessId>
                        			<taskId>' . $task_id . '</taskId>
                        			<updateTargetName i:nil="true" xmlns:i="http://www.w3.org/2001/XMLSchema-instance" />
                        		</ResumeTask>
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
        		<a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/ResumeTaskResponse
        		</a:Action>
        		<a:RelatesTo>urn:uuid:35a3bc1a-d3c1-4612-a58e-1026a8a9fb2b</a:RelatesTo>
        	</s:Header>
        	<s:Body>
        		<ResumeTaskResponse xmlns="http://tempuri.org/">
        			<ResumeTaskResult>true</ResumeTaskResult>
        		</ResumeTaskResponse>
        	</s:Body>
        </s:Envelope>
         */
        return (( string ) $result->children ( 's', true )->Body->children ( 'http://tempuri.org/' )->ResumeTaskResponse->ResumeTaskResult)=='true'?true:false;
    }
}