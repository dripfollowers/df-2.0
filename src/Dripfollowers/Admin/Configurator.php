<?php

namespace DripFollowers\Admin;

use DripFollowers\DripFollowers;
use DripFollowers\Common\DripFollowersConstants;

class Configurator {
    private $_plugin;

    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
        add_action ( 'admin_menu', array (&$this,'add_admin_menu') );
        add_action ( 'admin_init', array (&$this,'register_options') );
    }
    
    public function add_admin_menu() {
        $main_page = 'edit.php?post_type=' . DripFollowersConstants::CPT_DRIP_ORDER;
        $settings_page = add_submenu_page ( $main_page, __ ( 'Settings' ), __ ( 'Settings' ), 'manage_options', 'drip_settings', array (&$this,'render') );
    }
    

    private function echo_field_id($field) {
        echo DripFollowersConstants::OPT_OPTIONS_NAME . '_' . $field;
    }

    private function echo_field_name($field) {
        echo DripFollowersConstants::OPT_OPTIONS_NAME . '[' . $field . ']';
    }

    private function get_field_value($field) {
        $setting = esc_attr ( $this->_plugin->get_setting ( $field ) );
        if ($field == DripFollowersConstants::OPT_UPSELL_NUMBER_PERCENTAGE || $field == DripFollowersConstants::OPT_UPSELL_PRICE_PERCENTAGE)
            $setting = floatval ( $setting ) * 100;
        return $setting;
    }

    private function echo_field_value($field) {
        echo $this->get_field_value ( $field );
    }

    public function register_options() {
        register_setting ( DripFollowersConstants::OPT_OPTIONS_GROUP, DripFollowersConstants::OPT_OPTIONS_NAME, array (&$this,'sanitize_settings') );
    }

    public function sanitize_settings($input) {
        /*
         * if($this->service_availability_changed($input)){ set_transient(DripFollowersConstants::OPT_SERVICE_DOWN_CHANGED, true, 12 * HOUR_IN_SECONDS); }
         */
        if (isset ( $input [DripFollowersConstants::OPT_UPSELL_NUMBER_PERCENTAGE] ))
            $input [DripFollowersConstants::OPT_UPSELL_NUMBER_PERCENTAGE] = absint ( $input [DripFollowersConstants::OPT_UPSELL_NUMBER_PERCENTAGE] ) / 100;
        if (isset ( $input [DripFollowersConstants::OPT_UPSELL_PRICE_PERCENTAGE] ))
            $input [DripFollowersConstants::OPT_UPSELL_PRICE_PERCENTAGE] = absint ( $input [DripFollowersConstants::OPT_UPSELL_PRICE_PERCENTAGE] ) / 100;
        
        return $input;
    }

    private function service_availability_changed($input) {
        if (isset ( $input [DripFollowersConstants::OPT_LIKES_AVAILABILITY] )) {
            if ($this->_plugin->get_setting ( DripFollowersConstants::OPT_LIKES_AVAILABILITY ) != $input [DripFollowersConstants::OPT_LIKES_AVAILABILITY])
                return true;
        }
        if (isset ( $input [DripFollowersConstants::OPT_EXPRESS_AVAILABILITY] )) {
            if ($this->_plugin->get_setting ( DripFollowersConstants::OPT_EXPRESS_AVAILABILITY ) != $input [DripFollowersConstants::OPT_EXPRESS_AVAILABILITY])
                return true;
        }
        if (isset ( $input [DripFollowersConstants::OPT_DRIPPED_AVAILABILITY] )) {
            if ($this->_plugin->get_setting ( DripFollowersConstants::OPT_DRIPPED_AVAILABILITY ) != $input [DripFollowersConstants::OPT_DRIPPED_AVAILABILITY])
                return true;
        }
        return false;
    }

    public function render() {
        // $this->_plugin->get_logger()->addDebug('render', array($_POST, $_GET, $_REQUEST));
        ?>
<div class="wrap">
	<h2><?php _e( 'Drip Followers Options', 'drip-followers' ); ?></h2>
            <?php if( isset($_GET['settings-updated']) ) { ?>
                <div id="message" class="updated">
		<p>
			<strong><?php _e('Settings saved.') ?></strong>
		</p>
	</div>
            <?php } ?>

        	<form action="options.php" method="post" id="drip-followers">
        	<?php settings_fields( DripFollowersConstants::OPT_OPTIONS_GROUP ); ?>
        	<table class="form-table">
			<tr valign="top">
				<th scope="row"><label
					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_INSTAGRAM_SERVICE_URL); ?>">
        					<?php _e( 'Instagram Service URL', 'drip-followers' ); ?>
        				</label></th>
				<td><input type="text"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_INSTAGRAM_SERVICE_URL); ?>"
					value="<?php $this->echo_field_value(DripFollowersConstants::OPT_INSTAGRAM_SERVICE_URL); ?>"
					id="<?php $this->echo_field_name(DripFollowersConstants::OPT_INSTAGRAM_SERVICE_URL); ?>"
					class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label
					for="<?php $this->echo_field_name(DripFollowersConstants::OPT_INSTAGRAM_SERVICE_ACCESS_ID); ?>">
        					<?php _e('Instagram Access ID', 'drip-followers')?>
        				</label></th>
				<td><input type="text"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_INSTAGRAM_SERVICE_ACCESS_ID); ?>"
					value="<?php $this->echo_field_value(DripFollowersConstants::OPT_INSTAGRAM_SERVICE_ACCESS_ID); ?>"
					id="<?php $this->echo_field_id(DripFollowersConstants::OPT_INSTAGRAM_SERVICE_ACCESS_ID); ?>"
					class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label
					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_INSTAGRAM_CLIENT_ID); ?>">
        					<?php _e( 'Instagram Client ID', 'drip-followers' ); ?>
        				</label></th>
				<td><input type="text"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_INSTAGRAM_CLIENT_ID); ?>"
					value="<?php $this->echo_field_value(DripFollowersConstants::OPT_INSTAGRAM_CLIENT_ID); ?>"
					id="<?php $this->echo_field_name(DripFollowersConstants::OPT_INSTAGRAM_CLIENT_ID); ?>"
					class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label
					for="<?php $this->echo_field_name(DripFollowersConstants::OPT_INSTAGRAM_ACCESS_TOKEN); ?>">
        					<?php _e('Instagram Access Token', 'drip-followers')?>
        				</label></th>
				<td><input type="text"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_INSTAGRAM_ACCESS_TOKEN); ?>"
					value="<?php $this->echo_field_value(DripFollowersConstants::OPT_INSTAGRAM_ACCESS_TOKEN); ?>"
					id="<?php $this->echo_field_id(DripFollowersConstants::OPT_INSTAGRAM_ACCESS_TOKEN); ?>"
					class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label
					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_PAYPAL_ACCOUNT); ?>">
        					<?php _e('Paypal Account', 'drip-followers')?>
        				</label></th>
				<td><input type="text"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_PAYPAL_ACCOUNT); ?>"
					value="<?php $this->echo_field_value(DripFollowersConstants::OPT_PAYPAL_ACCOUNT ); ?>"
					id="<?php $this->echo_field_id(DripFollowersConstants::OPT_PAYPAL_ACCOUNT); ?>"
					class="regular-text" /></td>
			</tr>
			<!--  
        			<tr valign="top">
        				<th scope="row"><label
        					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_PAYPAL_RETURN_PAGE); ?>">
        					<?php _e('Paypal Return page', 'drip-followers')?>
        				</label></th>
        				<td>
        				<?php echo get_bloginfo('wpurl') . '/' ; ?><input type="text"
        					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_PAYPAL_RETURN_PAGE) ?>"
        					value="<?php $this->echo_field_value(DripFollowersConstants::OPT_PAYPAL_RETURN_PAGE); ?>"
        					id="<?php $this->echo_field_id(DripFollowersConstants::OPT_PAYPAL_RETURN_PAGE) ?>"
        					class="medium-text" />
        				</td>
        			</tr>
        			-->
			<tr valign="top">
				<th scope="row"><label
					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_UPSELL_NUMBER_PERCENTAGE); ?>">
        					<?php _e('Upsell Number Percentage', 'drip-followers')?>
        				</label></th>
				<td><input type="text"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_UPSELL_NUMBER_PERCENTAGE); ?>"
					value="<?php $this->echo_field_value(DripFollowersConstants::OPT_UPSELL_NUMBER_PERCENTAGE); ?>"
					id="<?php $this->echo_field_id(DripFollowersConstants::OPT_UPSELL_NUMBER_PERCENTAGE); ?>"
					class="tiny-text" />%</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label
					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_UPSELL_PRICE_PERCENTAGE); ?>">
                            <?php _e('Upsell Discount Percentage', 'drip-followers')?>
                        </label></th>
				<td><input type="text"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_UPSELL_PRICE_PERCENTAGE); ?>"
					value="<?php $this->echo_field_value(DripFollowersConstants::OPT_UPSELL_PRICE_PERCENTAGE); ?>"
					id="<?php $this->echo_field_id(DripFollowersConstants::OPT_UPSELL_PRICE_PERCENTAGE); ?>"
					class="tiny-text" />%</td>
			</tr>
			<tr valign="top">
				<th scope="row">
                        <?php _e('Express Followers Availability', 'drip-followers')?>
                    </th>
				<td><input type="radio"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_EXPRESS_AVAILABILITY); ?>"
					value="on"
					id="<?php $this->echo_field_id(DripFollowersConstants::OPT_EXPRESS_AVAILABILITY); ?>"
					<?php checked('on', $this->get_field_value(DripFollowersConstants::OPT_EXPRESS_AVAILABILITY)); ?> />
					<label
					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_EXPRESS_AVAILABILITY); ?>"><?php _e('Active', 'drip-followers'); ?></label><br />
					<input type="radio"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_EXPRESS_AVAILABILITY); ?>"
					value="off"
					id="<?php $this->echo_field_id(DripFollowersConstants::OPT_EXPRESS_AVAILABILITY); ?>"
					<?php checked('off', $this->get_field_value(DripFollowersConstants::OPT_EXPRESS_AVAILABILITY)); ?> />
					<label
					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_EXPRESS_AVAILABILITY); ?>"><?php _e('Down', 'drip-followers'); ?></label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
                        <?php _e('Dripped Followers Availability', 'drip-followers')?>
                    </th>
				<td><input type="radio"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_DRIPPED_AVAILABILITY); ?>"
					value="on"
					id="<?php $this->echo_field_id(DripFollowersConstants::OPT_DRIPPED_AVAILABILITY); ?>"
					<?php checked('on', $this->get_field_value(DripFollowersConstants::OPT_DRIPPED_AVAILABILITY)); ?> />
					<label
					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_DRIPPED_AVAILABILITY); ?>"><?php _e('Active', 'drip-followers'); ?></label><br />
					<input type="radio"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_DRIPPED_AVAILABILITY); ?>"
					value="off"
					id="<?php $this->echo_field_id(DripFollowersConstants::OPT_DRIPPED_AVAILABILITY); ?>"
					<?php checked('off', $this->get_field_value(DripFollowersConstants::OPT_DRIPPED_AVAILABILITY)); ?> />
					<label
					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_DRIPPED_AVAILABILITY); ?>"><?php _e('Down', 'drip-followers'); ?></label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
                        <?php _e('Instagram Likes Availability', 'drip-followers')?>
                    </th>
				<td><input type="radio"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_LIKES_AVAILABILITY); ?>"
					value="on"
					id="<?php $this->echo_field_id(DripFollowersConstants::OPT_LIKES_AVAILABILITY); ?>"
					<?php checked('on', $this->get_field_value(DripFollowersConstants::OPT_LIKES_AVAILABILITY)); ?> />
					<label
					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_LIKES_AVAILABILITY); ?>"><?php _e('Active', 'drip-followers'); ?></label><br />
					<input type="radio"
					name="<?php $this->echo_field_name(DripFollowersConstants::OPT_LIKES_AVAILABILITY); ?>"
					value="off"
					id="<?php $this->echo_field_id(DripFollowersConstants::OPT_LIKES_AVAILABILITY); ?>"
					<?php checked('off', $this->get_field_value(DripFollowersConstants::OPT_LIKES_AVAILABILITY)); ?> />
					<label
					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_LIKES_AVAILABILITY); ?>"><?php _e('Down', 'drip-followers'); ?></label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label
					for="<?php $this->echo_field_id(DripFollowersConstants::OPT_SERVICE_DOWN_MSG); ?>">
        					<?php _e( 'Service Down Message', 'drip-followers' ); ?>
        				</label></th>
				<td><textarea
						name="<?php $this->echo_field_name(DripFollowersConstants::OPT_SERVICE_DOWN_MSG); ?>"
						id="<?php $this->echo_field_name(DripFollowersConstants::OPT_SERVICE_DOWN_MSG); ?>"
						rows="10" cols="50" class="large-text code"><?php $this->echo_field_value(DripFollowersConstants::OPT_SERVICE_DOWN_MSG); ?></textarea></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="Submit" class="button button-primary"
				value="<?php _e('Update Options', 'drip-followers'); ?>" />
		</p>
	</form>
</div>
<?php
    }
}