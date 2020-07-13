<h1>Tasks checker</h1>
<form method="post">
Tasks: <input type="text" name="tasks" />
<br />
<input type="submit">
</form>
<div>
<?php
if(isset($_POST['tasks'])){
    
    $tasks = explode(',', trim($_POST['tasks'])) ;
    $accessId = 'dripfollowers::9283lsnoa239l((tcuoeh';
    $server = 'http://52.70.17.159:80';
    
    $data = '<s:Envelope xmlns:s="http://www.w3.org/2003/05/soap-envelope" xmlns:a="http://www.w3.org/2005/08/addressing">
              <s:Header>
                <a:Action s:mustUnderstand="1">http://tempuri.org/IOrderExecutorBot/GetTaskStatus</a:Action>
                <a:MessageID>urn:uuid:' . uniqid () . '</a:MessageID>
                <a:ReplyTo>
                  <a:Address>http://www.w3.org/2005/08/addressing/anonymous</a:Address>
                </a:ReplyTo>
                <a:To s:mustUnderstand="1">' . $server . '</a:To>
              </s:Header>
              <s:Body>
                <GetTaskStatus xmlns="http://tempuri.org/">
                  <accessId>' . $accessId . '</accessId>
                  <taskIds xmlns:b="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
            ';
    
            foreach ( $tasks as $task ) {
                $data .= '<b:unsignedLong>' . $task. '</b:unsignedLong>';
            }
    
    $data .= '        </taskIds>
                  <forceReadFromDatabase>true</forceReadFromDatabase>
                </GetTaskStatus>
              </s:Body>
            </s:Envelope>';
    
    
    $ch = curl_init ( $server );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
    curl_setopt ( $ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/soap+xml') );
    $result = curl_exec ( $ch );
    curl_close ( $ch );
    
    $xml = simplexml_load_string ( $result );
    
    $tasksResponses = $xml->children ( 's', true )->Body->children ( 'http://tempuri.org/' )->GetTaskStatusResponse->GetTaskStatusResult->children ( 'b', true );
    
    foreach ( $tasksResponses as $taskStatus ) {
        echo '--------------' . "<br />";
        echo 'task_id : ' . (( string ) $taskStatus->TaskId) . "<br />";
        echo 'not_found : '. (( string ) $taskStatus->NotFound)  . "<br />";
        echo 'is_completed : '. (( string ) $taskStatus->IsCompleted) . "<br />";
        echo '--------------' . "<br />";;
    }
    echo $result;
    return $result;
    
}
?>
</div>





