<?php
namespace DripFollowers\Admin\Stats;

use DripFollowers\DripFollowers;

abstract class PieChartAdapter {
    
    protected $_plugin;
    protected $_title;
    protected $_series_name;
    protected $_is_client_data_managed = false;
    
    public function __construct(DripFollowers $plugin){
        $this->_plugin = $plugin;
    }
    
    abstract protected function get_raw_data($options);
    abstract protected function get_series($rawData);
    
    public function generate_chart_data($options=null){
        $chartData = new PieChartDataValueObject();
        $chartData->title = $this->_title;
        $chartData->series_name = $this->_series_name;
        if(!$this->_is_client_data_managed){
            $rawData = $this->get_raw_data($options);
            $series_data = $this->get_series($rawData);
            $chartData->series_data = $series_data;
        }
        // $this->_plugin->get_logger()->addDebug('PieChartAdapter - generate_chart_data', array($chartData));
        return $chartData;
    }
    
    protected function get_total($rawData) {
        $total = 0;
        foreach ($rawData as $record){
            $total += $record->value;
        }
        return $total;
    }
    
}