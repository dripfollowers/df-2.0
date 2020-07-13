<?php
namespace DripFollowers\Admin\Stats;

use DripFollowers\DripFollowers;
use DripFollowers\Common\PacksTypes;

class PackSharesChart extends PieChartAdapter {
    
    protected $_type;
    protected  $_packs;
    protected  $_is_for_earning = false;
    
    public function __construct(DripFollowers $plugin, $type, $options=null){
        parent::__construct($plugin);
        $this->_type = $type;
        $this->_packs = $plugin->packRepo->get_packs($type);
        
        if(isset($options) && isset($options['earning']) && $options['earning']==true){
            $this->_is_for_earning = true;
        }
        
        $title = 'Packs Distribution';
        $by = ' by Number';
        if($this->_is_for_earning){
            $by = ' by Earning';
        } 
        if(PacksTypes::Instant_Followers==$type){
            $title = "Instant Followers Packs Distribution" ;
        } elseif (PacksTypes::Automatic_Followers==$type){
            $title = "Automatic Followers Packs Distribution";
        } elseif (PacksTypes::Instant_Likes==$type){
            $title = "Instant Likes Packs Distribution";
        } elseif (PacksTypes::Automatic_Likes==$type){
            $title = "Automatic Likes Packs Distribution";
        } elseif (PacksTypes::Instant_Views==$type){
            $title = "Instant Views Packs Distribution";
        } 
        $this->_title = $title . $by;
        $this->_seriesName = "Ordered Packs Sharing";
    }
    
    protected function get_raw_data($options){
        $options['is_for_earning'] = $this->_is_for_earning;
        $options['type'] = $this->_type ;
        $result = $this->_plugin->statsRepo->get_pack_shares_stats($options);
        // $this->_plugin->get_logger()->addDebug('SalesProgressChart - get_raw_data', array($result));
        return $result;
    }

    protected function get_series($rawData){
        $series = array();
            foreach ($rawData as $pack){
				if($pack != null){
                    if($this->_packs[$pack->code]){
                        $label = $this->_packs[$pack->code]->get_label();
                    } else {
                        $label = 'Offer no longer available';
                    }
                $val = 0;
                if($this->_is_for_earning){
                    $val = round((float)$pack->value, 2);
                } else {
                    $val = (int)$pack->value;
                }
                $series[] = array($label, $val);
				}
            }
        return $series;
    }
}