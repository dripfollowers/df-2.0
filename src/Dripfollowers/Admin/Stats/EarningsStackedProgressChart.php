<?php
namespace DripFollowers\Admin\Stats;

use DripFollowers\DripFollowers;

class EarningsStackedProgressChart extends LineChartAdapter {
    
    public function __construct(DripFollowers $plugin){
        parent::__construct($plugin);
        $this->_is_client_data_managed = true;
        $this->_title = 'Earnings Progress';
        $this->_y_axis_title = 'Total Paymant ($)';
    }
    
    public function get_raw_data($options){
    }

    public function get_series($data, $xaxis){
    }
}