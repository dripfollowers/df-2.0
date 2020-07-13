<?php
namespace DripFollowers\Admin\Stats;

use DripFollowers\DripFollowers;

class SalesProgressChart extends LineChartAdapter {
    
    public function __construct(DripFollowers $plugin){
        parent::__construct($plugin);
        $this->_title = 'Sales Progress By Product';
        $this->_y_axis_title = 'Valid Orders Number';
        $this->_tooltip_value_suffix = ' Orders';
    }
    
    public function get_raw_data($options){
        $result = $this->_plugin->statsRepo->get_orders_count_stats($options);
        // $this->_plugin->get_logger()->addDebug('SalesProgressChart - get_raw_data', array($result));
        return $result;
    }

    public function get_series($data, $xaxis){
        $series = array();
        
        foreach ($this->_series_map as $service=>$label){
            $values = array();
            foreach ($xaxis as $period){
                $key = $period.' '.$service;
                $values[] = isset($data[$key])?(int)$data[$key]->count:0;
            }
            $series[$label] = $values;
        }
        return $series;
    }
}