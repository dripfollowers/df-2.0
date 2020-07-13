<?php

namespace DripFollowers\Admin;

use DripFollowers\DripFollowers;
use DripFollowers\Common\DripFollowersConstants;
use DripFollowers\Admin\Stats\ChartDataFacade;

class Stats {

    private $_plugin;

    public function __construct(DripFollowers $plugin){
        $this->_plugin = $plugin;

        add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts' ));
        add_action( 'admin_menu', array ($this,'add_stats_page') );

    }

    public function add_stats_page() {
        $main_page = 'edit.php?post_type=' . DripFollowersConstants::CPT_DRIP_ORDER;
        $stats_page = add_submenu_page ( $main_page, 'Drip Followers Stats', 'Stats', 'manage_options', 'order-stats', array ($this,'render') );
    }

    public function enqueue_scripts( $hook ) {
        if ( 'driporder_page_order-stats' == $hook ) {
            wp_register_script(
                'highcharts',
                $this->_plugin->get_plugin_url() . 'js/highcharts/highcharts.js',
                array( 'jquery' ),
                '3.0.10',
                true
            );
            wp_register_script(
                'adminProgressCharts',
                $this->_plugin->get_plugin_url() . 'js/progress_stats.js',
                array( 'highcharts' ),
                '1.0',
                true
            );
            wp_register_script(
                'adminPieCharts',
                $this->_plugin->get_plugin_url() . 'js/pie_stats.js',
                array( 'highcharts' ),
                '1.O',
                true
            );
            //
            $data = ChartDataFacade::get_chart_data_extractor('all', $this->_plugin);
            wp_localize_script(
                'adminProgressCharts', 
                'data',
                $data
            );
            wp_localize_script(
                'adminPieCharts',
                'data',
                $data
            );
            //
            wp_enqueue_script( 'adminProgressCharts' );
            wp_enqueue_script( 'adminPieCharts' );
        }
    }

    public function render() {
    ?>
    <div class="wrap">
    	<h2>Orders Stats</h2>
    	<div class="welcome-panel">
    		<div class="welcome-panel-content">
        	  <div id="stacked-earnings" style="min-width: 310px; height: 250px; margin: 0 auto"></div>
    		</div>
    	</div>
    	<div class="welcome-panel">
    		<div class="welcome-panel-content">
    		  <div id="earnings" style="min-width: 310px; height: 250px; margin: 0 auto"></div>
    		</div>
    	</div>
    	<div class="welcome-panel">
    		<div class="welcome-panel-content">
        	  <div id="sales" style="min-width: 310px; height: 250px; margin: 0 auto"></div>
    		</div>
    	</div>
    	<div id="dashboard-widgets-wrap">
    		<div id="dashboard-widgets" class="metabox-holder">
    			<div id="postbox-container-1" class="postbox-container">
    				<div id="" class="meta-box-sortables ui-sortable">
    					<div id="" class="postbox ">
    						<div class="inside">
    						  <div id="express-pie" style="min-width: 310px; height: 450px; margin: 0 auto"></div>
    						</div>
    					</div>
    					<div id="" class="postbox ">
                            <div class="inside">
    						  <div id="likes-pie" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    						</div>
    					</div>
    					<div id="" class="postbox ">
                            <div class="inside">
    						  <div id="daily-pie" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    						</div>
    					</div>
                        <div id="" class="postbox ">
                            <div class="inside">
                              <div id="auto-likes-pie" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                            </div>
                        </div>
                        <div id="" class="postbox ">
                            <div class="inside">
                              <div id="views-pie" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                            </div>
                        </div>
    				</div>
    			</div>
    			<div id="postbox-container-2" class="postbox-container">
    				<div id="" class="meta-box-sortables ui-sortable">
    					<div id="" class="postbox ">
    						<div class="inside">
    						  <div id="express-earning-pie" style="min-width: 310px; height: 450px; margin: 0 auto"></div>
    						</div>
    					</div>
    					<div id="" class="postbox ">
                            <div class="inside">
    						  <div id="likes-earning-pie" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    						</div>
    					</div>
    					<div id="" class="postbox ">
                            <div class="inside">
    						  <div id="daily-earning-pie" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
    						</div>
    					</div>
                        <div id="" class="postbox ">
                            <div class="inside">
                              <div id="auto-likes-earning-pie" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                            </div>
                        </div>
                        <div id="" class="postbox ">
                            <div class="inside">
                              <div id="views-earning-pie" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
                            </div>
                        </div>
    				</div>
    			</div>
    		</div>
    
    	</div>
    </div>
    <?php
    }

} 