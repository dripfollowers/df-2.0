<?php

namespace DripFollowers\Common;

class Pack {
    public $_id;
    public $_code;
    public $_label;
    public $_type;
    public $_is_active;
    public $_subject;
    public $_base_number;
    public $_base_price;
    public $_with_upsell = false;
    public $_upsell_number_percentage = 0.35;
    public $_upsell_price_percentage = 0.25;
    public $_number_per_day;
    private $_drip_base_days = 30;

    public function set_is_active($is_active) {
        $this->_is_active = $is_active;
    }

    public function get_is_active() {
        return $this->_is_active;
    }

    public function get_type() {
        return $this->_type;
    }

    public function set_type($type) {
        $this->_type = $type;
    }

    public function set_subject($subject) {
        $this->_subject = $subject;
    }

    public function get_subject() {
        return $this->_subject;
    }

    public function set_with_upsell($with_upsell) {
        $this->_with_upsell = $with_upsell;
    }

    public function is_with_upsell() {
        return $this->_with_upsell;
    }

    public function set_upsell_number_percentage($upsell_number_percentage) {
        $this->_upsell_number_percentage = $upsell_number_percentage;
    }

    public function get_upsell_number_percentage() {
        return $this->_upsell_number_percentage;
    }

    public function set_upsell_price_percentage($upsell_price_percentage) {
        $this->_upsell_price_percentage = $upsell_price_percentage;
    }

    public function get_upsell_price_percentage() {
        return $this->_upsell_price_percentage;
    }

    public function set_id($id) {
        $this->_id = $id;
    }

    public function set_code($code) {
        $this->_code = $code;
    }

    public function set_label($name) {
        $this->_label = $name;
    }

    public function set_base_number($base_number) {
        $this->_base_number = intval ( $base_number );
    }

    public function set_base_price($base_price) {
        $this->_base_price = floatval ( $base_price );
    }

    public function get_id() {
        return $this->_id;
    }

    public function get_code() {
        return $this->_code;
    }

    public function get_label() {
        return $this->_label;
    }

    public function get_base_number() {
        return intval ( $this->_base_number );
    }

    public function get_base_price() {
        return round ( $this->_base_price, 2 );
    }

    public function get_upsell_number() {
        // if ( !$this->_with_upsell )
        // return 0;
        return intval ( $this->_base_number * $this->get_upsell_number_percentage () );
    }

    public function get_upsell_price() {
        // return $this->_base_price - $this->get_upsell_discount();
        return round ( $this->_base_price / $this->_base_number * $this->get_upsell_number () * (1 - $this->get_upsell_price_percentage ()), 2 );
    }

    public function get_upsell_discount() {
        // if ( !$this->_with_upsell )
        // return 0;
        return round ( $this->_base_price / $this->_base_number * $this->get_upsell_number () * $this->get_upsell_price_percentage (), 2 );
    }

    public function get_number() {
        $number = $this->_base_number;
        if ($this->_with_upsell)
            $number += $this->get_upsell_number ();
        return $number;
    }

    public function get_price() {
        $price = $this->_base_price;
        if ($this->_with_upsell)
            $price += $this->get_upsell_price ();
        return $price;
    }

    public function set_number_per_day($number_per_day) {
        $this->_number_per_day = $number_per_day;
    }

    public function get_number_per_day() {
        // return $this->_number_per_day;
        return ceil ( $this->get_number () / $this->_drip_base_days );
    }
    
    public function get_approximative_number_per_day() {
        // return $this->_number_per_day;
        $number = $this->get_number () / $this->_drip_base_days;
        if(is_int($number))
            return $number;
        if(is_float($number)){
            return floor( $number ) . '/' . ceil ( $number );
        }
    }

    public function get_drip_delay() {
        // 86400000 day in milliseconds
        return intval( 86400000 / $this->get_number_per_day () );
    }

    public function __toString() {
        // return serialize($this);
        return 'Pack: {id: ' . $this->get_id () . ', code: ' . $this->get_code () . ', type:' . $this->get_type () . ', upsell: ' . $this->is_with_upsell () . ', number: ' . $this->get_number () . ', number_per_day: ' . $this->get_number_per_day () . ', price: ' . $this->get_price ();
    }
}