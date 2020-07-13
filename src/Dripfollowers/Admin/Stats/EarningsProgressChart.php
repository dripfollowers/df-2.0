<?php
namespace DripFollowers\Admin\Stats;

use DripFollowers\DripFollowers;

class EarningsProgressChart extends LineChartAdapter {
    
    public function __construct(DripFollowers $plugin){
        parent::__construct($plugin);
        $this->_title = 'Earnings Progress By Product';
        $this->_y_axis_title = 'Total Paymant ($)';
        $this->_tooltip_value_suffix = ' $';
    }
    
    public function get_raw_data($options){
        $result = $this->_plugin->statsRepo->get_orders_earnings_stats($options);
        // $this->_plugin->get_logger()->addDebug('EarningsProgressChart - get_raw_data', array($result));
        return $result;
    }

    public function get_series($data, $xaxis){
        $series = array();
        
        foreach ($this->_series_map as $service=>$label){
            $values = array();
            foreach ($xaxis as $period){
                $key = $period.' '.$service;
                $values[] = isset($data[$key])?(float)$data[$key]->earning:0;
            }
            $series[$label] = $values;
        }
        return $series;
    }
}