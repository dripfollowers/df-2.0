<?php

namespace DripFollowers\Service\Api;

use DripFollowers\DripFollowers;
use DripFollowers\Common\DripFollowersConstants;

abstract class InstagramOBJAutoLService {
    protected $_service_url;
    protected $_service_access_id;
    protected $_data;
    protected $_type;
    protected $_plugin;

    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
        $this->_service_url = "192.99.57.32:8070/";
        $this->_service_access_id = "c78cf6a72a2346d5a11c595a67cc1891";
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
        $this->_plugin->get_logger ()->addDebug ( 'InstagramOtlService - Sending Instagram Service Data', array ($log_data) );
        $ch = curl_init ( $this->_service_url . $this->_data );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/x-www-form-urlencoded') );
        $result = curl_exec ( $ch );
        curl_close ( $ch );
        $this->_plugin->get_logger ()->addDebug ( 'InstagramOtlService - Instagram Service Response', array ($result) );
        
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