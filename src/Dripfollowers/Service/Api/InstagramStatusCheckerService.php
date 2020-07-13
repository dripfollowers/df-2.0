<?php

namespace DripFollowers\Service\Api;

class InstagramStatusCheckerService extends InstagramService {

    protected function set_data($params) {
        // $this->_plugin->get_logger()->addDebug('InstagramStatusCheckerService - set_data with params', array($params));
        $orders = $params ['orders'];
        if (is_array ( $orders ) && ! empty ( $orders )) {
            $this->_data = '<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope" xmlns:a="http://www.w3.org/2005/08/addressing">
              <s:Header>
                <a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/GetTaskStatus</a:Action>
                <a:MessageID>urn:uuid:' . uniqid () . '</a:MessageID>
                <a:ReplyTo>
                  <a:Address>http://www.w3.org/2005/08/addressing/anonymous</a:Address>
                </a:ReplyTo>
                <a:To s:mustUnderstand="1">' . $this->_service_url . '</a:To>
              </s:Header>
              <s:Body>
                <GetTaskStatus xmlns="http://tempuri.org/">
                  <accessId>' . $this->_service_access_id . '</accessId>
                  <taskIds xmlns:b="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
            ';
            
            foreach ( $orders as $order ) {
                $this->_data .= '<b:unsignedLong>' . $order->task_id . '</b:unsignedLong>';
            }
            
            $this->_data .= '        </taskIds>
                  <forceReadFromDatabase>true</forceReadFromDatabase>
                </GetTaskStatus>
              </s:Body>
            </s:Envelope>';
        }
        
        // $this->_plugin->get_logger()->addDebug('InstagramStatusCheckerService - set_data to send', array($this->_data));
    }

    protected function parse_result($xml) {
        /*
         * Reponse format
         * 
         <s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope"
        	xmlns:a="http://www.w3.org/2005/08/addressing">
        	<s:Header>
        		<a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/GetTaskStatusResponse
        		</a:Action>
        		<a:RelatesTo>urn:uuid:531b05337949e</a:RelatesTo>
        	</s:Header>
        	<s:Body>
        		<GetTaskStatusResponse xmlns="http://tempuri.org/">
        			<GetTaskStatusResult
        				xmlns:b="http://schemas.datacontract.org/2004/07/order_executor_4.OutboundInterface"
        				xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
        				<b:TaskStatus>
        					<b:ActionsIndicator>2</b:ActionsIndicator>
        					<b:DelayedUntil i:nil="true" />
        					<b:Error />
        					<b:InitialCount>1</b:InitialCount>
        					<b:IsCompleted>true</b:IsCompleted>
        					<b:NotFound>false</b:NotFound>
        					<b:TargetActions>2</b:TargetActions>
        					<b:TaskId>723250</b:TaskId>
        				</b:TaskStatus>
        				<b:TaskStatus>
        				</b:TaskStatus>
        			</GetTaskStatusResult>
        		</GetTaskStatusResponse>
        	</s:Body>
        </s:Envelope>
 
         */
        $tasks = $xml->children ( 's', true )->Body->children ( 'http://tempuri.org/' )->GetTaskStatusResponse->GetTaskStatusResult->children ( 'b', true );
        $result = array ();
        foreach ( $tasks as $taskStatus ) {
            $task = new \stdClass ();
            $task->task_id = ( string ) $taskStatus->TaskId;
            $task->is_completed = (( string ) $taskStatus->IsCompleted) == "true" ? true : false;
            $task->not_found = (( string ) $taskStatus->NotFound) == "true" ? true : false;
            $task->initial_count = ( string ) $taskStatus->InitialCount;
            $result [$task->task_id] = $task;
        }
        return $result;
    }
}