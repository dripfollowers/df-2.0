<?php

namespace DripFollowers\Service\Repo;

use DripFollowers\DripFollowers;
use DripFollowers\Common\PacksTypes;
use DripFollowers\Common\DripFollowersConstants;
use DripFollowers\Common\ProgressStatus;
use DripFollowers\Common\Pack;

class StatsRepository {
    private $_plugin;

    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
    }
    
    private function append_conditions($options){
        $sql = '';
        if(null!= $options && isset($options['conditions'])){
            //curent week
            //$options = array('conditions'=>array('WEEKOFYEAR( o.post_date ) = WEEKOFYEAR( NOW( ) )'));
            //last week
            //$options = array('conditions'=>array('WEEKOFYEAR( o.post_date ) = WEEKOFYEAR( NOW( ) ) -1'));
            foreach ($options['conditions'] as $condition){
                $sql .= " AND ".$condition;
            }
        }
        return $sql;
    }
    
    public function get_orders_count_stats($options=null){
        global $wpdb;
        $sql = "SELECT CONCAT(DATE_FORMAT(o.post_date, '%b %Y '), s.meta_value) AS ukey, DATE_FORMAT(o.post_date, '%b %Y') AS period, 
        YEAR(o.post_date) AS year, MONTH(o.post_date) AS month, s.meta_value AS service, 
        COUNT(*) AS count
        FROM $wpdb->posts AS o, $wpdb->postmeta AS s, $wpdb->postmeta AS v
        WHERE o.id = s.post_id
        AND o.id = v.post_id
        AND o.post_type = 'driporder'
        AND s.meta_key = 'order-service'
        AND v.meta_key = 'order-payment-status'
        AND v.meta_value = 'VALID'";
        $sql .= $this->append_conditions($options);
        $sql .= " GROUP BY service, year, month
        ORDER BY year, month";
        
        return $wpdb->get_results($sql, OBJECT_K);
    }
    
    public function get_orders_earnings_stats($options=null){
        global $wpdb;
        $sql = "SELECT CONCAT(DATE_FORMAT(o.post_date, '%b %Y '), s.meta_value) AS ukey,
        DATE_FORMAT(o.post_date, '%b %Y') AS period, YEAR(o.post_date) AS year,
        MONTH(o.post_date) AS month, s.meta_value AS service, ROUND(SUM(e.meta_value), 2) AS earning
        FROM $wpdb->posts AS o, $wpdb->postmeta AS s, $wpdb->postmeta AS e,
        $wpdb->postmeta AS v
        WHERE o.id = s.post_id
        AND o.id = e.post_id
        AND o.id = v.post_id
        AND o.post_type = 'driporder'
        AND s.meta_key = 'order-service'
        AND e.meta_key = 'order-payment-amount'
        AND v.meta_key = 'order-payment-status'
        AND v.meta_value = 'VALID'";
        $sql .= $this->append_conditions($options);
        $sql .= " GROUP BY service, year, month
        ORDER BY year, month";
        return $wpdb->get_results($sql, OBJECT_K);
    }
    
    public function get_pack_shares_stats($options){
        global $wpdb;
        $sql = '';
        if($options['is_for_earning']){
            $sql .= "SELECT c.meta_value AS code, s.meta_value AS service, ROUND(SUM(p.meta_value), 2) AS value
                FROM $wpdb->posts AS o, $wpdb->postmeta AS c,
                $wpdb->postmeta AS s, $wpdb->postmeta AS p, $wpdb->postmeta AS v
                WHERE o.id = c.post_id
                AND o.id = s.post_id
                AND o.id = p.post_id
                AND o.id = v.post_id
                AND o.post_type = 'driporder'
                AND s.meta_key =  'order-service'
                AND s.meta_value =  '".$options['type']."'
                AND c.meta_key =  'order-pack'
                AND p.meta_key = 'order-payment-amount'
                AND v.meta_key = 'order-payment-status'
                AND v.meta_value = 'VALID' ";
        } else {
            $sql .= "SELECT p.meta_value AS code, s.meta_value AS service, COUNT(p.meta_value) AS value
                FROM $wpdb->posts AS o, $wpdb->postmeta AS p, $wpdb->postmeta AS s, $wpdb->postmeta AS v
                WHERE o.id = p.post_id
                AND o.id = s.post_id
                AND o.id = v.post_id
                AND o.post_type = 'driporder'
                AND s.meta_key = 'order-service'
                AND s.meta_value = '".$options['type']."'
                AND p.meta_key = 'order-pack'
                AND v.meta_key = 'order-payment-status'
                AND v.meta_value = 'VALID' ";
        }
        $sql .= $this->append_conditions($options);
        $sql .= " GROUP BY code";
        return $wpdb->get_results($sql, OBJECT);
    }
    
}