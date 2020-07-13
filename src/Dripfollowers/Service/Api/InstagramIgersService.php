<?php

namespace DripFollowers\Service\Api;

use DripFollowers\DripFollowers;
use DripFollowers\Common\DripFollowersConstants;

abstract class InstagramIgersService {
    protected $_service_url;
    protected $_service_access_id;
    protected $_data;
    protected $_type;
    protected $_plugin;

    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
 
        $this->_service_url = "https://www.igerslike.com/api/order/add";  
      //  $this->_service_url = "https://www.igerslike.com/api/automatic/order/add"; 
      //  $this->_service_access_id = "56807caf9774df3b0eb39398186a95c624e9fb612266e8547df9a7a1071457de";
        $this->_service_access_id = "lnrwe9tiw56tipd266fgtn4utt6fasp1jap49aqgmf0v1sat6udaprucupukzfks";
        
    }

    abstract protected function set_data($params);

    abstract protected function parse_result($result);

    public function send_request($params) {
        try {
            $this->set_data ( $params );
            $this->check_params ();
            $result = $this->send ();
            if ($result) {
                return $this->parse_result ( json_decode ( $result ) );
            }
        } catch ( \Exception $e ) {
            $this->_plugin->get_logger ()->addError ( 'InstagramService -Invalid Instagram Service Request: ' . $e->getMessage (), array () );
        }
    }

    private function send() {
        $log_data = str_replace ( $this->_service_access_id, '*******', $this->_data );
        $this->_plugin->get_logger ()->addDebug ( 'InstagramIgersService - Sending Instagram Service Data', array ($log_data) );
        $ch = curl_init ( $this->_service_url );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $this->_data );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/x-www-form-urlencoded') );
        $result = curl_exec ( $ch );
        curl_close ( $ch );
        $this->_plugin->get_logger ()->addDebug ( 'InstagramIgersService - Instagram Service Response', array ($result) );
        
        return $result;
    }

    private function check_params() {
        if (! isset ( $this->_data ))
            throw new \InvalidArgumentException ( "Missing Data to send" );
        if (! isset ( $this->_service_url ))
            throw new \InvalidArgumentException ( "Missing Service URL" );
        if (! isset ( $this->_service_access_id ))
            throw new \InvalidArgumentException ( "Missing Service Access ID" );
    }
}