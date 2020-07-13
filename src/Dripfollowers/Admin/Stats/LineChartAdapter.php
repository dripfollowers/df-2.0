<?php
namespace DripFollowers\Admin\Stats;

use DripFollowers\DripFollowers;

abstract class LineChartAdapter {
    
    protected $_plugin;
    protected $_title;
    protected $_y_axis_title;
    protected $_tooltip_value_suffix;
    protected $_is_client_data_managed=false;
    
    protected $_series_map = array(
            'instant-followers'=>'Instant Followers',
            'automatic-followers'=>'Automatic Followers',
            'instant-likes'=>'Instant Likes',
            'automatic-likes'=>'Automatic Likes',
            'instant-views'=>'Instant Views'
    );
    
    public function __construct(DripFollowers $plugin){
        $this->_plugin = $plugin;
    }
    
    abstract function get_raw_data($options);
    abstract function get_series($data, $xaxis);
    
    public function generate_chart_data($options=null){
        $chartData = new LineChartDataValueObject();
        $chartData->title = $this->_title;
        $chartData->y_axis_title = $this->_y_axis_title;
        $chartData->tooltip_value_suffix = $this->_tooltip_value_suffix;
        if(!$this->_is_client_data_managed){
            $data = $this->get_raw_data($options);
            $xaxis = $this->get_x_axis_categories($data);
            $series = $this->get_series($data, $xaxis);
        
            $chartData->x_axis_categories = $xaxis;
            $chartData->series = $series;
            $chartData->series_labels = $this->_series_map;
        }
    
        // $this->_plugin->get_logger()->addDebug('LineChartAdapter - generate_chart_data', array($chartData));
        return $chartData;
    }
    
    private function get_x_axis_categories($data) {
        $xaxis_categories = array ();
    
        $start_element = reset ( $data );
        $end_element = end ( $data );
        $m_start = ( int ) $start_element->month;
        $y_start = ( int ) $start_element->year;
        $m_end = ( int ) $end_element->month;
        $y_end = ( int ) $end_element->year;
    
        for($y = $y_start; $y <= $y_end; $y ++) {
            $real_m_start = 1;
            $real_m_end = 12;
            if ($y == $y_start) {
                $real_m_start = $m_start;
            }
            if ($y == $y_end) {
                $real_m_end = $m_end;
            }
            for($m = $real_m_start; $m <= $real_m_end; $m ++) {
                $xaxis_categories [] = date ( "M", mktime ( 0, 0, 0, $m, 10 ) ) . ' ' . $y;
            }
        }
    
        $this->_plugin->get_logger()->addDebug('LineChartAdapter - get_x_axis_categories', array($xaxis_categories));
        return $xaxis_categories;
    }
    
}