<?php

namespace DripFollowers\ShortCode;

use DripFollowers\DripFollowers;
use DripFollowers\Common\PacksTypes;

class ThankYouHandler {
    private $_plugin;

    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
        add_shortcode ( 'thank-you', array (&$this,'render' 
        ) );
    }

    public function render() {
        try {
            $pack_type = sanitize_text_field ( $_GET ['pack'] );
            $pack_subject = sanitize_text_field ( $_GET ['pack_subject'] );
            $number = sanitize_text_field ( $_GET ['number'] );
            $price = sanitize_text_field ( $_GET ['price'] );
            if (! isset ( $number ) || ! isset ( $pack_subject ) || ! isset ( $price )) {
                throw new \Exception ( 'Missing param' );
            if (! PacksTypes::is_valide_type ( $pack_type ))
                throw new \Exception ( 'Unknown type' );
            }
        } catch ( \Exception $e ) {
            $this->_plugin->get_logger()->addError("ThankYouHandler - " . $e->getMessage(), array($pack_type, $pack_subject, $number, $price));
        }
        
        if (isset ( $number ) && isset ( $pack_subject ) && isset ( $price )) {
            ?>


<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({
  'event': 'transaction',
  'ecommerce': {
    'purchase': {
      'actionField': {
        'id': '<?php echo uniqid(); ?>',
        'revenue': '<?php echo $price; ?>'
      },
      'products': [{                            // List of productFieldObjects.
        'name': '<?php echo $number; ?> <?php echo $pack_type; ?>',
        'price': '<?php echo $price; ?>',
        'quantity': 1,
        'category': '<?php echo $pack_type; ?>'
      }]
    }
  }
 });
</script>

<?php 

switch($pack_type){
    case 'instant-likes':
        $message = 'Your Instagram Likes will Start soon.';
        break;
    case 'instant-followers':
        $message = 'Your Instagram Followers will Start soon.';
        break;
    case 'automatic-likes':
        $message = 'You are all set, time to upload!';
        break;
    case 'automatic-followers':
        $message = 'Your Automatic Instagram Followers will Start soon.';
        break;
    case 'instant-views':
        $message = 'Your Instagram Views will Start soon.';
        break;
    default:
        $message = 'Thank you for your order';
}

?>

<div id="thank_you" class="bloc">
	<!-- Logo -->
	<!-- End Logo -->
	<!-- Head -->
	<div class="row preview-head">
		<div class="col-xs-12 col-md-8">
			<h2>
				Plan: <span><?php echo $number; ?> Guaranteed <?php echo $pack_subject; ?></span>
			</h2>
			<p><?php echo $message; ?></p>
		</div>

		<div class="col-xs-12 col-md-4">
			<h3>$<?php echo $price; ?></h3>
		</div>

	</div>
	<hr>
	<!-- End Head -->

	<!-- Core -->
	<div class="row preview-core">
		<div class="col-xs-12">
			<h4>Contact Us</h4>
			<p class="text-left">
				If you have any question or concerns you can contact us at any
				time using the email below <br /> <a
					href="mailto:<?php echo antispambot('Support@fluidbuzz.com') ?>."><?php echo antispambot('Support@fluidbuzz.com') ?></a>
			</p>
		</div>
	</div>
	<!-- Core -->
</div>

<?php
        }
    }
}