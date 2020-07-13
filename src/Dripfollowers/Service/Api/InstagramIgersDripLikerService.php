<?php

namespace DripFollowers\Service\Api;

class InstagramIgersDripLikerService extends InstagramIgersService {
    private $_daily_likes;

    public function __construct($plugin, $delay) {
        parent::__construct ( $plugin );
        $this->_daily_likes = ceil(1/($delay/86400000)); // $delay is ms between each follow
	// $this->_service_url = "https://www.igerslike.com/api/automatic/order/add";	
    }

    protected function set_data($params) {
        $target = $params ['target'];
		if (strpos($target, 'instagram.com') === false)
			$target = urlencode('https://instagram.com/' . $target);
        $count = $params ['count'];
		/*key - Your private key
action - automatic_add
type - The type of product to delivery , check Shop - Add order for the service list
link - The web link to delivery likes/followers or actions
amount_total - The amount total of followers/likes or actions to be delivered, the order will stop/finish once hits this limit
amount_per_run - The amount of followers/likes or actions to delivery every X seconds ( every run )
delay - Numeric fields with the number of seconds to pause between each order ( 1 minute = 60 seconds / 1 day = 86400 seconds )
*/
		$delay_between_runs = 0;
		$likes_per_run = 0;
		$_daily_likes = $this->_daily_likes;
		if ($_daily_likes >= 30) {                       // 50
			$delay_between_runs = 86400;               // 1 days
			$likes_per_run = $_daily_likes ;                     // 
		}
		else {
	                 // put bacl june 7 was since 2017  change feb 2019 
	                    	//	$delay_between_runs = ceil(3600*(24/($_daily_likes/100)));     // 25
	                    	$delay_between_runs = ceil(3600*(24/($_daily_likes/100)));     // 25
		             //put bacl june 7  was since 2017 chnge feb 2019       	
		                        $likes_per_run = 100;     // 25
		             //or
	
	   //  change 20 feb 2019 and remove 7 june 	$delay_between_runs = ceil(3600*(24/($_daily_likes/30)));     // 50
		
		 // chnge f20eb 2019  and remove 7 june   	$likes_per_run = 30;     // 50
		}

	//removed &replace20fev 2019
		//$delay_between_runs = 86400;   // new feb 2019
	//removed & reolace 20fev 2019	
	     // $likes_per_run = $_daily_likes ; //		new  feb 2019$delay_between_runs = 86200;
//		$likes_per_run = 10;
	//	$this->_data = 'key=' . $this->_service_access_id . '&action=automatic_add&type=ig_followers&link='.$target.'&amount_total='.$count.'&amount_per_run='.$likes_per_run.'&delay='.$delay_between_runs;
        $this->_data = 'Key=' . $this->_service_access_id . '&ProductId=1&Amount=' .$likes_per_run . '&AmountTotal=' . $count .'&Link=' . $target.'&Pause='.$delay_between_runs;


     $this->_service_url = "https://www.igerslike.com/api/automatic/order/add";
 //  $this->_service_url = "https://www.igerslike.com/api/order/add";
    }
//   "Key" => "56807caf9774df3b0eb39398186a95c624e9fb612266e8547df9a7a1071457de",
//    "ProductId" => 1, // Instagram - Likes
//    "Amount" => 5, // How much we should place per order ( ex 5 Likes )
//    "AmountTotal" => 1000, // Your target amount ( ex 5 likes until 1000 completed )
//    "Link" => "https://instagram.com/p/xpto", // The link to deliverey
//    "Pause" => 3000,  // The pause time between each order ( Ex delivery 5 likes pause X minutes <repeat> )

    protected function parse_result($result) {
        /*
         * Reponse format
         *
        {"status":"ok","message":"Order Automatic Added","order":"123456","link":"http://instagram.com/justinbieber","amount_target":"10000","amount_per_run":"1000","delay":"86400"}

		{"status":"fail","message":"Error Message describing the problem"}

        */
        return ( string ) $result->{'order'};
    }
}