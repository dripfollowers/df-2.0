<?php
/**
* Plugin Name: Drip Followers
* Description: Drip Followers
* Version: 1.1
* Author: Agafix Labs
*/
namespace DripFollowers;

set_time_limit(0);
require dirname ( __FILE__ ) . '/vendor/autoload.php';
include_once 'config.php';

use DripFollowers\Common\DripFollowersConstants;
use DripFollowers\Common\PacksTypes;
use DripFollowers\Admin\OrderViews;
use DripFollowers\Admin\Configurator;
use DripFollowers\Admin\Stats;
use DripFollowers\Admin\QuickStats;
use DripFollowers\ShortCode\BuyingWizard;
use DripFollowers\ShortCode\ThankYouHandler;
use DripFollowers\ShortCode\ServiceDownMsg;
use DripFollowers\Service\Repo\PackRepository;
use DripFollowers\Service\Repo\OrderRepository;
use DripFollowers\Service\Repo\StatsRepository;
use DripFollowers\Service\InstagramInfoChecker;
use DripFollowers\Service\PayPalIpnHandler;
use DripFollowers\Service\InstagramStatusCheckerCron;
use DripFollowers\Service\InstagramReTryReScheduledCron;
use DripFollowers\Service\InstagramGiftProcessor;
use Carbon\Carbon;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ChromePHPHandler;

class DripFollowers {
    private static $_log;
    private static $_log_dir = __DIR__;
    private static $_settings;
    private static $_plugin_url;
    private static $_defaults = array (
            DripFollowersConstants::OPT_SANDBOX => DRIP_FOLLOWERS_SANDBOX,
            DripFollowersConstants::OPT_INSTAGRAM_CLIENT_ID => '',
            DripFollowersConstants::OPT_INSTAGRAM_ACCESS_TOKEN => '',
            DripFollowersConstants::OPT_PAYPAL_ACCOUNT => '',
            DripFollowersConstants::OPT_PAYPAL_RETURN_PAGE => 'thank-you',
            DripFollowersConstants::OPT_UPSELL_NUMBER_PERCENTAGE => 0.35,
            DripFollowersConstants::OPT_UPSELL_PRICE_PERCENTAGE => 0.25,
            DripFollowersConstants::OPT_EXPRESS_AVAILABILITY => 'on',

            DripFollowersConstants::OPT_VIEWS_AVAILABILITY => 'on',

            DripFollowersConstants::OPT_DRIPPED_AVAILABILITY => 'on',
            DripFollowersConstants::OPT_LIKES_AVAILABILITY => 'on' 
    );
    private static $_paypal_url = array (
            'sandbox' => 'https://www.sandbox.paypal.com/webscr',
            'live' => 'https://www.paypal.com/webscr' 
    );
    public $instagramInfoChecker;
    public $packRepo;
    public $orderRepo;
    public $statsRepo;
    public $giftProcessor;

    public function __construct() {
        self::$_log = self::get_logger ();
        
        $this->_load_settings ();
        self::$_plugin_url = defined ( 'WP_PLUGIN_URL' ) ? trailingslashit ( WP_PLUGIN_URL . '/' . dirname ( plugin_basename ( __FILE__ ) ) ) : trailingslashit ( get_bloginfo ( 'wpurl' ) ) . PLUGINDIR . '/' . dirname ( plugin_basename ( __FILE__ ) );
        
        add_filter ( 'cron_schedules', array ($this,'cron_add_15_minutely') );
        if(DRIP_FOLLOWERS_LOG_TO_FILE){
            add_action ( 'wp', array (&$this,'logger_roller_schedule') );
            add_action ( 'logger_roller_cron', array (&$this,'roll_logger_file') );
        }
        
        $this->packRepo = new PackRepository ( $this );
        $this->orderRepo = new OrderRepository ( $this );
        $this->statsRepo = new StatsRepository( $this );
        $this->instagramInfoChecker = new InstagramInfoChecker ( $this );
        $this->giftProcessor = new InstagramGiftProcessor( $this );
        new OrderViews ( $this );
        new Stats( $this );
        new Configurator ( $this );
        new QuickStats ( $this );
        new BuyingWizard ( $this );
        new ThankYouHandler ( $this );
        new PayPalIpnHandler ( $this );
        new InstagramStatusCheckerCron ( $this );
        new InstagramReTryReScheduledCron ( $this );
    }

    function is_service_active($type) {
        $service_state = "off";
        switch ($type) {
            case PacksTypes::Instant_Views :
                $service_state = self::get_setting ( DripFollowersConstants::OPT_VIEWS_AVAILABILITY );
                break;
            case PacksTypes::Instant_Followers :
                $service_state = self::get_setting ( DripFollowersConstants::OPT_EXPRESS_AVAILABILITY );
                break;
            case PacksTypes::Automatic_Followers :
                $service_state = self::get_setting ( DripFollowersConstants::OPT_DRIPPED_AVAILABILITY );
                break;
            case PacksTypes::Instant_Likes :
                $service_state = self::get_setting ( DripFollowersConstants::OPT_LIKES_AVAILABILITY );
                break;
        }
        return $service_state == "on";
    }

    function is_service_down() {
        if (! $this->is_service_active ( PacksTypes::Instant_Views ))
            return true;
        if (! $this->is_service_active ( PacksTypes::Instant_Followers ))
            return true;
        if (! $this->is_service_active ( PacksTypes::Automatic_Followers ))
            return true;
        if (! $this->is_service_active ( PacksTypes::Instant_Likes ))
            return true;
        
        return false;
    }

    function echo_service_down_msg() {
        $helper = new ServiceDownMsg ( $this );
        echo $helper->render ();
    }

    private function _load_settings() {
        if (empty ( self::$_settings ))
            self::$_settings = get_option ( DripFollowersConstants::OPT_OPTIONS_NAME );
        if (! is_array ( self::$_settings ))
            self::$_settings = array ();
        
        self::$_settings = wp_parse_args ( self::$_settings, self::$_defaults );
    }

    public static function get_setting($setting_name, $default = false) {
        if (empty ( self::$_settings ))
            self::_load_settings ();
        if (isset ( self::$_settings [$setting_name] ))
            return self::$_settings [$setting_name];
        else
            return $default;
    }

    public static function get_plugin_url() {
        return self::$_plugin_url;
    }

    public function get_return_page_url() {
        return get_bloginfo ( 'wpurl' ) . '/' . self::get_setting ( DripFollowersConstants::OPT_PAYPAL_RETURN_PAGE );
    }

    public function get_ipn_notify_url() {
        return add_query_arg ( array ('action' => 'paypal_ipn_listener' 
        ), admin_url ( 'admin-ajax.php' ) );
    }

    public function get_paypal_url() {
        if (self::get_setting ( DripFollowersConstants::OPT_SANDBOX ) == false)
            return self::$_paypal_url ['live'];
        else
            return self::$_paypal_url ['sandbox'];
    }

    function cron_add_15_minutely($schedules) {
        $schedules ['15minutely'] = array (
                'interval' => 900,
                'display' => __ ( 'Every 15 Minutes' ) 
        );
        $schedules ['minutely'] = array (
                'interval' => 60,
                'display' => __ ( 'Every 2 Minute' )
        );
        $schedules ['2hourly'] = array (
                'interval' => 7200,
                'display' => __ ( 'Every 2 Hours' )
        );
        return $schedules;
    }

    public static function get_logger() {
        if (null == self::$_log) {
            self::$_log = new Logger ( 'app' );
            //
            //
            if(DRIP_FOLLOWERS_LOG_TO_FILE){
                $handler = new StreamHandler ( self::$_log_dir . '/log/log.log', DRIP_FOLLOWERS_LOG_LEVEL=='ERROR'?Logger::ERROR:Logger::DEBUG );
                $handler->setFormatter(new LineFormatter("[%datetime%] %channel%.%level_name%: %message% %extra% %context%\n\n"));
                self::$_log->pushHandler ( $handler );
            }
            self::$_log->pushHandler ( new ChromePHPHandler () );
        }
        return self::$_log;
    }
    
    function logger_roller_schedule() {
        if (!wp_next_scheduled ( 'logger_roller_cron' )) {
            $midnightTodayEasternTime = Carbon::createFromTime(0, 0, 0, 'America/New_York')->timestamp;
            wp_schedule_event ( $midnightTodayEasternTime, 'daily', 'logger_roller_cron' );
        }
    }
    
    function roll_logger_file(){
        self::get_logger()->addDebug('DripFollowers - rolling logger file');
        $currentFile = self::$_log_dir . '/log/log.log';
        $tmpFile = self::$_log_dir . '/log/log.tmp';
        $newName = self::$_log_dir . '/log/log-'.Carbon::yesterday()->toDateString().'.log';
        if(!file_exists($newName)){
            if (copy($currentFile, $tmpFile)) {
                if(rename($tmpFile, $newName)){
                    file_put_contents($currentFile, '');
                }
            }
        }
    }
}
$dripFollowers = new DripFollowers ();