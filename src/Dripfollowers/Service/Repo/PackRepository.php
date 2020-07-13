<?php

namespace DripFollowers\Service\Repo;

use DripFollowers\Common\Pack;
use DripFollowers\Common\PacksTypes;
use DripFollowers\Common\DripFollowersConstants;

class PackRepository {
    private $_plugin;

    public function __construct($plugin) {
        $this->_plugin = $plugin;
    }

    public function get_packs($type, $code = null) {
        $packs = array ();
        $args = array (
                'post_type' => $type,
                'posts_per_page'=>-1
        );
        if (null != $code) {
            $args ['meta_query'] = array (
                    array (
                            'key' => 'code',
                            'value' => $code 
                    ) 
            );
        }
        
        $the_query = new \WP_Query ( $args );
        if ($the_query->have_posts ()) {
            $is_service_active = $this->_plugin->is_service_active ( $type );
            while ( $the_query->have_posts () ) {
                $the_query->the_post ();
                $pack = new Pack ();
                $id = get_the_ID ();
                $pack->set_id ( $id );
                $code = get_post_meta ( $id, 'code', true );
                $pack->set_code ( $code );
                $pack->set_upsell_price_percentage ( $this->_plugin->get_setting ( DripFollowersConstants::OPT_UPSELL_PRICE_PERCENTAGE ) );
                $pack->set_upsell_number_percentage ( $this->_plugin->get_setting ( DripFollowersConstants::OPT_UPSELL_NUMBER_PERCENTAGE ) );
                $pack->set_is_active ( $is_service_active );
                $pack->set_label ( get_the_title () );
                $pack->set_base_number ( get_post_meta ( $id, 'number', true ) );
                $pack->set_base_price ( get_post_meta ( $id, 'price', true ) );
                if ($type == PacksTypes::Automatic_Followers || $type == PacksTypes::Automatic_Likes ) {
                    $pack->set_number_per_day ( get_post_meta ( $id, 'number_per_day', true ) );
                }
                $pack->set_type ( $type );
                $pack->set_subject ( get_post_meta ( $id, 'subject', true ) );
                $packs [$code] = $pack;
            }
        }
        wp_reset_postdata ();
        return $packs;
    }

    public function get_pack($type, $code) {
        $packs = $this->get_packs ( $type, $code );
        if (isset ( $packs [$code] )) {
            return $packs [$code];
        }
        return null;
    }

    public function get_pack_by_order($orderId) {
        $type = get_post_meta ( $orderId, DripFollowersConstants::COL_ORDER_SERVICE, true );
        $code = get_post_meta ( $orderId, DripFollowersConstants::COL_ORDER_PACK, true );
        
        $pack = $this->_plugin->packRepo->get_pack ( $type, $code );
        $pack->set_with_upsell ( get_post_meta ( $orderId, DripFollowersConstants::COL_ORDER_WITH_UPSELL, true ) );
        
        $pack->set_upsell_price_percentage ( $this->_plugin->get_setting ( DripFollowersConstants::OPT_UPSELL_PRICE_PERCENTAGE ) );
        $pack->set_upsell_number_percentage ( $this->_plugin->get_setting ( DripFollowersConstants::OPT_UPSELL_NUMBER_PERCENTAGE ) );
        
        return $pack;
    }
}