<?php

namespace DripFollowers\Admin;

use DripFollowers\DripFollowers;
use DripFollowers\Common\PacksTypes;

class QuickStats {

    const THIS_WEEK = 0;
    const LAST_WEEK = 1; 
    private $_plugin;

    public function __construct(DripFollowers $plugin){
        $this->_plugin = $plugin;
        
        add_action( 'wp_dashboard_setup', array($this, 'add_dashboard_quick_stats_widgets') );
    }

    function add_dashboard_quick_stats_widgets() {
    	wp_add_dashboard_widget(
             'quick_orders_stats_widget', 
             "This week's orders stats",
             array($this, 'render') )
        ;
    }
    
    public function get_stats_by_week($week, $earnings, $counts){
        //
        $types = array(
            PacksTypes::Instant_Followers, 
            PacksTypes::Automatic_Followers, 
            PacksTypes::Instant_Likes, 
            PacksTypes::Automatic_Likes, 
            PacksTypes::Instant_Views
        );
        //
        $stats=array();
        foreach ($types as $type){
            $stats[$type]['count']=0;
            $stats[$type]['earning']=0;
        }
        foreach ($earnings as $earning){
            $stats[$earning->service]['earning'] = (float) $earning->earning;
        }
        foreach ($counts as $count){
            $stats[$count->service]['count'] = (int) $count->count;
        }
        
        $stats['total_count']=0;
        $stats['total_earning']=0;
        foreach ($types as $type){
            $stats['total_count'] += $stats[$type]['count'];
            $stats['total_earning'] += $stats[$type]['earning'];
        }
        return $stats;
    }
    
    public function load_stats(){
        $options = array('conditions'=>array('WEEKOFYEAR( o.post_date ) = WEEKOFYEAR( NOW() )'));
        $earnings = $this->_plugin->statsRepo->get_orders_earnings_stats($options);
        $counts = $this->_plugin->statsRepo->get_orders_count_stats($options);
        $stats[self::THIS_WEEK] = $this->get_stats_by_week(self::THIS_WEEK, $earnings, $counts);
        //
        $options = array('conditions'=>array('WEEKOFYEAR( o.post_date ) = WEEKOFYEAR( NOW() ) -1'));
        $earnings = $this->_plugin->statsRepo->get_orders_earnings_stats($options);
        $counts = $this->_plugin->statsRepo->get_orders_count_stats($options);
        $stats[self::LAST_WEEK] = $this->get_stats_by_week(self::LAST_WEEK, $earnings, $counts);
        
        $this->_plugin->get_logger ()->addDebug('QuickStats - load_stats', array($stats, $earnings, $counts));
        return $stats;
    }

    function render() {
        $stats = $this->load_stats();
    ?>
            <div class="main"> 
            	<div style="float: left; width: 50%">
            	   <div data-code="f174" class="dashicons dashicons-cart"></div> You have earned <span style="font-size: larger; font-weight: bold; color: #39c439">$<?php echo $stats[self::THIS_WEEK]['total_earning']; ?></span>
            		<ul>
            			<li><div data-code="f469" class="dashicons dashicons-clock"></div> Instant Followers: $<?php echo $stats[self::THIS_WEEK][PacksTypes::Instant_Followers]['earning']; ?></li>
            			<li><div data-code="f145" class="dashicons dashicons-calendar"></div> Automatic Followers: $<?php echo $stats[self::THIS_WEEK][PacksTypes::Automatic_Followers]['earning']; ?></li>
            			<li><div data-code="f232" class="dashicons dashicons-images-alt"></div> Instant Likes: $<?php echo $stats[self::THIS_WEEK][PacksTypes::Instant_Likes]['earning']; ?></li>
                        <li><div data-code="f469" class="dashicons dashicons-clock"></div> Automatic Likes: $<?php echo $stats[self::THIS_WEEK][PacksTypes::Automatic_Likes]['earning']; ?></li>
                        <li><div data-code="f236" class="dashicons dashicons-video-alt3"></div> Views: $<?php echo $stats[self::THIS_WEEK][PacksTypes::Instant_Views]['earning']; ?></li>
            		</ul>
            	</div>
            	<div style="float: left; width: 50%">
            	   <div data-code="f239" class="dashicons dashicons-chart-area"></div> You have sold <span style="font-size: larger;"><?php echo $stats[self::THIS_WEEK]['total_count'] ?></span> orders
            		<ul>
            			<li><div data-code="f469" class="dashicons dashicons-clock"></div> Instant Followers: <?php echo $stats[self::THIS_WEEK][PacksTypes::Instant_Followers]['count']; ?></li>
            			<li><div data-code="f145" class="dashicons dashicons-calendar"></div> Automatic Followers: <?php echo $stats[self::THIS_WEEK][PacksTypes::Automatic_Followers]['count']; ?></li>
                        <li><div data-code="f232" class="dashicons dashicons-images-alt"></div> Instant Likes: <?php echo $stats[self::THIS_WEEK][PacksTypes::Instant_Likes]['count']; ?></li>
                        <li><div data-code="f469" class="dashicons dashicons-clock"></div> Automatic Likes: <?php echo $stats[self::THIS_WEEK][PacksTypes::Automatic_Likes]['count']; ?></li>
                        <li><div data-code="f236" class="dashicons dashicons-video-alt3"></div> Views: <?php echo $stats[self::THIS_WEEK][PacksTypes::Instant_Views]['count']; ?></li>
            		</ul>
            	</div>
            </div>
            <div class="main">
            	<h4 style="clear: both; border-bottom: 1px solid #eee; border-top: 1px solid #eee; padding: 10px 0px 10px 0px; margin-bottom: 10px; font-weight: bolder;">Last week's stats</h4>
            	<div style="float: left; width: 50%;">
            	   <div data-code="f174" class="dashicons dashicons-cart"></div> You have earned <span style="font-size: larger; font-weight: bold; color: #39c439">$<?php echo $stats[self::LAST_WEEK]['total_earning']; ?></span>
            		<ul>
            			<li><div data-code="f469" class="dashicons dashicons-clock"></div> Instant Followers: $<?php echo $stats[self::LAST_WEEK][PacksTypes::Instant_Followers]['earning']; ?></li>
            			<li><div data-code="f145" class="dashicons dashicons-calendar"></div> Automatic Followers: $<?php echo $stats[self::LAST_WEEK][PacksTypes::Automatic_Followers]['earning']; ?></li>
                        <li><div data-code="f232" class="dashicons dashicons-images-alt"></div> Instant Likes: $<?php echo $stats[self::LAST_WEEK][PacksTypes::Instant_Likes]['earning']; ?></li>
                        <li><div data-code="f469" class="dashicons dashicons-clock"></div> Automatic Likes: $<?php echo $stats[self::LAST_WEEK][PacksTypes::Automatic_Likes]['earning']; ?></li>
                        <li><div data-code="f236" class="dashicons dashicons-video-alt3"></div> Views: $<?php echo $stats[self::LAST_WEEK][PacksTypes::Instant_Views]['earning']; ?></li>
            		</ul>
            	</div>
            	<div style="float: left; width: 50%">
            	   <div data-code="f239" class="dashicons dashicons-chart-area"></div> You have sold <span style="font-size: larger;"><?php echo $stats[self::LAST_WEEK]['total_count'] ?></span> orders
            		<ul>
            			<li><div data-code="f469" class="dashicons dashicons-clock"></div> Instant Followers: <?php echo $stats[self::LAST_WEEK][PacksTypes::Instant_Followers]['count']; ?></li>
            			<li><div data-code="f145" class="dashicons dashicons-calendar"></div> Automatic Followers: <?php echo $stats[self::LAST_WEEK][PacksTypes::Automatic_Followers]['count']; ?></li>
                        <li><div data-code="f232" class="dashicons dashicons-images-alt"></div> Instant Likes: <?php echo $stats[self::LAST_WEEK][PacksTypes::Instant_Likes]['count']; ?></li>
                        <li><div data-code="f469" class="dashicons dashicons-clock"></div> Automatic Likes: <?php echo $stats[self::LAST_WEEK][PacksTypes::Automatic_Likes]['count']; ?></li>
                        <li><div data-code="f236" class="dashicons dashicons-video-alt3"></div> Views: <?php echo $stats[self::LAST_WEEK][PacksTypes::Instant_Views]['count']; ?></li>
            		</ul>
            	</div>
            </div>
        	<div style="clear: both"></div>
        	<p>Jump to full <a href="edit.php?post_type=driporder&page=order-stats">orders stats</a></p>
    <?php 
    }

} 