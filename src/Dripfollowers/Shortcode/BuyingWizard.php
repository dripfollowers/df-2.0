<?php

namespace DripFollowers\ShortCode;

use DripFollowers\DripFollowers;
use DripFollowers\Common\PacksTypes;
use DripFollowers\Common\InstagramTypes;

class BuyingWizard {
    private $_pack = null;
    private $_plugin;

    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
        add_shortcode ( 'buying_wizard', array (&$this,'render') );
        add_action ( 'wp_ajax_instagram_checker', array (&$this,'instagram_checker_callback' ) );
        add_action ( 'wp_ajax_nopriv_instagram_checker', array (&$this,'instagram_checker_callback' 
        ) );
        if (! is_admin ()) {
            add_action ( 'wp_head', array (&$this,'create_ajax_base_info') );
            add_action ( 'wp_print_scripts', array (&$this,'enqueue_script' ) );
            add_action ( 'wp_print_styles', array (&$this,'enqueue_style') );
        }
    }

    private function retreive_pack() {
        try {
            $pack_type = sanitize_text_field ( $_GET ['type'] );
            if (! PacksTypes::is_valide_type ( $pack_type ))
                throw new \Exception ( 'Unknown type ' . $pack_type );
            $pack_code = sanitize_text_field ( $_GET ['pack'] );
            if (isset ( $pack_code ) && isset ( $pack_type )) {
                $this->_pack = $this->_plugin->packRepo->get_pack ( $pack_type, $pack_code );
                //$this->_plugin->get_logger ()->addDebug ( 'Buying Wizard Pack', array ($pack_type,$pack_code,$this->_pack) );
            }
            if (! isset ( $this->_pack ) || ! is_object ( $this->_pack ))
                throw new \Exception ( 'Unknown pack' );
        } catch ( \Exception $e ) {
            $this->_plugin->get_logger ()->addDebug ( 'Buying Wizard Error', array ($e,$pack_type,$pack_code  ) );
        }
    }

    function instagram_checker_callback() {
        $nonce = $_POST ['instagram_checker_nonce'];
        if (! wp_verify_nonce ( $nonce, 'instagram_checker_nonce' )) {
            die ();
        }
        $result = array ();
        $type = sanitize_text_field ( $_POST ['type'] );
        $pack_type = sanitize_text_field ( $_POST ['args'] ['pack_type'] );
        // $is_service_active = $this->_plugin->is_service_active ( $pack_type );
        // if ($is_service_active) {
        $email = sanitize_text_field ( $_POST ['args'] ['email'] );
        $result ['email'] = $email;
        if (InstagramTypes::INSTAGRAM_PROFILE == $type) {
            $username = $this->extract_user_name( $_POST ['args'] ['username'] );
            $result ['username'] = $username;
            $user = $this->_plugin->instagramInfoChecker->search_username ( $username );
            if (isset ( $user ))
                $result ['target'] = $user;
        } elseif (InstagramTypes::INSTAGRAM_MEDIA == $type) {
            $media = sanitize_text_field ( $_POST ['args'] ['media'] );
            $result ['media_url'] = $media;
            $media_info = $this->_plugin->instagramInfoChecker->search_media ( $media );
            if (isset ( $media_info ))
                $result ['target'] = $media_info;
        }
        // } else {
        // $result ['service_down'] = "down";
        // }
        //$this->_plugin->get_logger ()->addDebug ( 'BuyingWizard - instagram_checker_callback', array ($_POST,json_encode ($result) ) );
        header ( "Content-Type: application/json" );
        echo json_encode ( $result );
		exit;
    }
    
    function extract_user_name($username){
        $regx = '/^(http.*\/\/)?(www.)?(instagram\.com\/)?(.*)\/?/i';
        preg_match($regx, $username, $matches);
        if(isset($matches[4])){
            return $matches[4];
        }
    }

    function create_ajax_base_info() {
        ?>
<script type="text/javascript">
		var InstagramCheckerAjax = {
		    "ajaxurl": "<?php echo admin_url('admin-ajax.php') ?>",
		    "instagram_checker_nonce": "<?php echo wp_create_nonce('instagram_checker_nonce') ?>"
		};
	</script>
<?php
    }

    function enqueue_script() {
        // wp_enqueue_script('jquery');
        wp_enqueue_script ( 'drip-followers.js', $this->_plugin->get_plugin_url () . 'js/script.js?4', array ('jquery' 
        ), '1.6' );
    }

    function enqueue_style() {
        wp_enqueue_style ( 'drip-followers.css', $this->_plugin->get_plugin_url () . 'css/style.css', array (), '1.0' );
    }

    public function render() {
        $this->retreive_pack ();
        if (isset ( $this->_pack )) {
            ?>

<div id="buying_wizard">

        <?php 
        $subject = '';
    	$message = '';
    	$icon = '';
    	$upsell_block = false;
        if ( PacksTypes::Instant_Likes == $this->_pack->get_type () ) {
            $upsell_subject = 'Likes';
          	$subject = 'Guaranteed Likes';
          	$icon = 'heart';
          	$message = 'Instagram Likes will start in minutes. Quick and Easy!';
          	$upsell_block = true;
        } elseif ( PacksTypes::Instant_Followers == $this->_pack->get_type () ){
            $upsell_subject = 'Followers';
        	$icon = 'followers';
        	$subject = 'Guaranteed Express Followers'; 
          	$message = 'Instagram Followers will start soon. Quick and Easy!';
          	$upsell_block = true;
        } elseif ( PacksTypes::Automatic_Followers == $this->_pack->get_type () ){ 
            $upsell_subject = '';
        	$icon = 'followers';
        	$subject = 'Automatic Followers Per Month'; 
          	$message = 'You will receive '.$this->_pack->get_number_per_day().' Automatic Followers per day, totaling to '.$this->_pack->get_number().' Followers per month, until your <b>subscription</b> is canceled.';
            if($this->_pack->get_code()=='pack-1'){ 
                $message = 'You will receive 50 Automatic Followers <b> every other day</b>, totaling to 750 Followers per month, until your subscription is canceled. <br><br>If you\'d like to receive daily followers, please select a <a href="/buy/?type=automatic-followers&pack=pack-2">larger pack</a>.'; 
            }
        } elseif ( PacksTypes::Automatic_Likes == $this->_pack->get_type () ){
            $upsell_subject = ''; 
        	$icon = 'heart';
        	$subject = 'Guaranteed Automatic Likes'; 
          	$message = 'Future uploads will receive '.$this->_pack->get_number().' likes & views (up To 3 posts per day). Likes & views will usually start a few minutes after you upload. We will automatically detect any of your future posts. Cancel your <b>subscription</b> anytime!';
        } elseif ( PacksTypes::Instant_Views == $this->_pack->get_type () ){ 
            $upsell_subject = '';
        	$icon = 'heart';
        	$subject = 'Instant Views'; 
          	$message = 'Instant Views will start in minutes. Quick and Easy!';
        } ?>

    <?php if ( $upsell_block ) { ?>
	<div class='upsell-bloc'>
		<div id='exit'>x</div>
		<p>Save Time and Money by Switching to <b>Automatic</b> <?php echo $upsell_subject; ?></p>
		<div class='text-center'>
			<a href="#" class='button semi-trans'>SWITCH TO AUTOMATIC</a>
		</div>
	</div>
	<?php } ?>

	<div id="step1">
		<div class='checkout-bloc'>
			<h2>Checkout</h2>

            <?php if ( PacksTypes::Instant_Likes == $this->_pack->get_type () || PacksTypes::Instant_Views == $this->_pack->get_type() ) { ?>
                <h3 class='step'><span>1</span> Enter Instagram Post ID (URL):</h3>
            <?php } else { ?>
                <h3 class='step'><span>1</span> Enter Instagram Account</h3>
			<?php } ?>

            <div class="error"></div>


            <div style='position:relative;'>
				<span class='icon-sprite <?php echo $icon; ?>'></span>
				<div class='checkout-form-row'>
        			<span class='plan-details'><?php echo $this->_pack->get_base_number().' '; echo $subject; ?></span>
        		</div>
            </div>

			<form id="check_form" class="check">

            <?php if ( PacksTypes::Instant_Likes == $this->_pack->get_type () || PacksTypes::Instant_Views == $this->_pack->get_type () ) { ?>
				<div style='position:relative;'>
					<span class='icon-sprite user'></span>
					<input name="media" type="text"
					class="champs checkout-form-row"
					placeholder="https://instagram.com/p/WJFGP9y3fffpI/"
					data-field="true" />
				</div>
            <?php } else { ?>
				<div style='position:relative;'>
					<span class='icon-sprite user'></span>
					<input type="text" class="champs checkout-form-row" name="username" data-field="true" placeholder="Your Instagram username" />
				</div>
            <?php } ?>
            	<div style='position:relative;'>
					<span class='icon-sprite email'></span>
					<input type="email" class="champs checkout-form-row" name="email" data-field="true" placeholder="Enter your email address" />
				</div>

				<div class="checkout-form-row additional-upsell">
					<input id="checkbox" type="checkbox" name="upsell" value="upsell"><label
						class="lowprice" for="checkbox"><?php printf( __('Add %d %s for %d%% off!', 'drip-followers' ), $this->_pack->get_upsell_number(), $this->_pack->get_subject(), $this->_pack->get_upsell_price_percentage()*100 );  ?></label>
				</div>

				<div class='checkout-extras'>
					<p>By clicking on 'Continue', you agree to our <a href="/terms-of-service">terms of service</a> and confirm that you've read our <a href="/privacy-policy">privacy policy</a>.</p>
					<input id="mailing-list-optin" type="checkbox" name="mailing-list-optin" value="mailing-list-optin">
					<label class="optin" for="checkbox">Subscribe for Newsletter and Offers</label>
				</div>

				<div>
					<input name="pack_code" type="hidden"
						value="<?php echo $this->_pack->get_code(); ?>" /> <input
						name="pack_type" type="hidden"
						value="<?php echo $this->_pack->get_type(); ?>" /> <input
						name="pack_subject" type="hidden"
						value="<?php echo $this->_pack->get_subject() ?>" /> <input
						name="pack_number" type="hidden"
						value="<?php echo $this->_pack->get_base_number(); ?>" /> <input
						name="pack_price" type="hidden"
						value="<?php echo $this->_pack->get_base_price(); ?>" /> <input
						name="upsell_number" type="hidden"
						value="<?php echo $this->_pack->get_upsell_number(); ?>" /> <input
						name="upsell_price" type="hidden"
						value="<?php echo $this->_pack->get_upsell_price(); ?>" /> <input
						name="submit" type="submit" value="Continue"
						class="button right radius" /> <span class="loader"
						style="display: none"></span>
				</div>
			</form>

		</div>
		<!-- End Check bloc -->

		<!-- Plan preview -->
		<!-- End Plan preview -->
	</div>


	<div id="step2" style="display: none">
		
		<!-- Check bloc -->
		<div class="checkout-bloc">
			<h2 class="text-center">Checkout</h2>
            <h3 class='step'><span>2</span> Review Details</h3>
						
            <hr>
            <div class='row flex'>
            	<div class='col-sm-8 flex'>
					<?php if (PacksTypes::Instant_Likes == $this->_pack->get_type () || PacksTypes::Instant_Views == $this->_pack->get_type()) { ?>
					<img id="thumb_preview" src="" alt="" />
                    <div class='user-details-wrap'>
                        <p class='user-details'><b>Email : </b> <span id="email" data-field="true"></p>
                    </div>
		            <?php } else {?>
					<img id="thumb_preview" src="" alt="" class="profile" />
					<div class='user-details-wrap'>
						<p class='user-details'><b>Username : </b> <span id="username" data-field="true"></p>
						<p class='user-details'><b>Email : </b> <span id="email" data-field="true"></p>
					</div>
                    <?php } ?>
				</div>
				<div class='col-sm-4 text-right return-col'>
					<a name="return" href="#" class="button left radius edit">Change</a>
				</div>
			</div>
		</div>

		<!-- Discount block -->
		<div class="discount-wrapper">
			<h3>
				Congratulation You're eligible for Special Discount! 
				<br>Apply Discount code to get the discount
			</h3>
			<form class="discount-form" id="discountForm">
				<input type="text" class="form-control" id="discountCode" name="discount-code" placeholder="Your Discount Code">
				<input type="submit" class="button" value="apply">
			</form>
			<p class="discountApplyResponse"></p>
		</div>

		<div class='checkout-bloc'>

			<div class='grey-wrap'>
				<div class='white-wrap'>
					<div style='position:relative;'>
						<span class='icon-sprite <?php echo $icon; ?>'></span>
						<span class='plan-details'>
						    <span id="total_number" data-field="true"></span> <?php echo $subject; ?>
						    </span>
					</div>
					<p><?php echo $message; ?></p>
				</div>
			</div>

			<div class='price-wrap'>
				<div class='row'>
					<div class='col-xs-6 text-left'>
						<span class='your-total'>Your total:</span>
					</div>
					<div class='col-xs-6 text-right'>
						<div class='price'>$<span id="total_price" data-field="true"></span></div>
					</div>
				</div>
			</div>

		</div>

		<div class='checkout-bloc'>
            <h3 class='step'><span>3</span> Proceed to Payment</h3>
			<p class="secured">You will be transferred to our secure payment gateway to complete your purchase.</p>

            <form action="http://192.99.57.32/payment/payment.php"
				method="post" id="checkout_form">
			<?php
            if( PacksTypes::Instant_Followers == $this->_pack->get_type () || PacksTypes::Instant_Likes == $this->_pack->get_type () || PacksTypes::Instant_Views == $this->_pack->get_type () ){
            ?>
				<input type="hidden" name="cmd" value="_cart"> 
				<input type="hidden" name="upload" value="1"> 
				<input type="hidden" name="item_name_1" value="<?php printf( __('Instagram %d %s', 'drip-followers'), $this->_pack->get_base_number(), $this->_pack->get_subject()); ?>">
				<input type="hidden" name="quantity_1" value="1"> 
				<input type="hidden" name="amount" value="<?php echo $this->_pack->get_base_price(); ?>"> 
				<input type="hidden" name="item_number_1" value="1"> 
            <?php } else { ?>
                <input type="hidden" name="cmd" value="_xclick-subscriptions">
                <input type="hidden" name="item_name" value="<?php printf( __('Instagram %d %s', 'drip-followers'), $this->_pack->get_base_number(), $this->_pack->get_subject()); ?>">
                <input type="hidden" name="amount" value="<?php echo $this->_pack->get_base_price(); ?>">
                <input type="hidden" name="p3" value="1">
                <input type="hidden" name="t3" value="M">
                <input type="hidden" name="src" value="1">
                <input type="hidden" name="sra" value="1">
            <?php }?>
				<input type="hidden" name="no_shipping" value="1"> 
				<input type="hidden" name="no_note" value="0"> 
				<input type="hidden" name="business" value="<?php echo $this->_plugin->get_setting('paypal-account'); ?>">
				<input type="hidden" name="currency_code" value="USD"> 
				<input type="hidden" name="custom" value=""> 
				<input type="hidden" name="return" value="<?php echo $this->_plugin->get_return_page_url() ; ?>"> 
				<input type="hidden" name="rm" value="2">
    				<input type="hidden" name="type" value="coinbase">
    				<input type="hidden" name="location" value="E93D6K0AGTCRX">
    				<input type="hidden" name="apiKey" value="fluidbuzz">
				<?php
				if(isset($_GET['discount']) && $_GET['discount'] > 0)
				{
					$notifyUrl = $this->_plugin->get_ipn_notify_url().'&paymentType=discount';
				}else
				{
					$notifyUrl = $this->_plugin->get_ipn_notify_url();
				}
				?>
				<input type="hidden" name="notify_url" value="<?php echo $notifyUrl; ?>"> 
				<input name="proceed" type="submit" value="Proceed to checkout" class="button right radius checkout" />
			</form>

		</div>
	</div>

	<div class="tpl" style="display: none;">
		<div data-result="service_down" class="result_block">
			<div data-alert class="alert-box warning radius">
            <?php _e( 'Unfortunately this service is down due to technical issue, please try again or contact the support if the problem persist ', 'drip-followers' ); ?>
        </div>
		</div>
		<div data-result="check_error" class="result_block">
			<div data-alert class="alert-box warning radius"><b>Error: </b>Could not get
				information about your account. Make sure your profile is set to
				public. Click "Edit Profile, then uncheck "Post are Private". After,
				you are good to go!</div>
		</div>
	</div>

</div>

<?php
		
        }
		
		
    }
}