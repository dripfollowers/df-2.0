<?php

namespace DripFollowers\Service;

use DripFollowers\Common\DripFollowersConstants;
use DripFollowers\Common\InstagramTypes;

class InstagramInfoChecker {
    private $_plugin;
    private $_logger;
    private $_access_token;
    private $_client_id;
    private $_instagram_endpoint = 'https://api.instagram.com/v1/';
    private $_embedding_endpoint = 'http://api.instagram.com/oembed?';
    private $_remote_get_args = array ('timeout' => 500);

    public function __construct($plugin) {
        $this->_plugin = $plugin;
        $this->_logger = $plugin->get_logger ();
        
        $this->set_access_token ( $plugin->get_setting ( DripFollowersConstants::OPT_INSTAGRAM_ACCESS_TOKEN ) );
        $this->set_client_id ( $plugin->get_setting ( DripFollowersConstants::OPT_INSTAGRAM_CLIENT_ID ) );
    }
    
    private function persistent_wp_remote_get($url, $args){
        $response = new \WP_Error();
        $j = 0;
        while(is_wp_error ( $response ) && $j<10){
            if($j>0){
                $this->_logger->addDebug ( 'InstagramInfoChecker - Error while contacting Instagram API, RETRYING' );
            }
            $response = wp_remote_get ( $url, $args );
            $j++;
        }
        return $response;
    }
    
    public function search_username($username) {
        $response = null;
        try {
            //$response = $this->persistent_wp_remote_get ( "http://198.100.154.7/imageSee.php?url=".$username."&type=user", $this->_remote_get_args );
         //return json_decode('{"entry_data":{"ProfilePage":[{"graphql":{"user":'.$response['body'].'}}]}}')->entry_data->ProfilePage[0]->graphql->user;
           	return json_decode('{"entry_data":{"ProfilePage":[{"graphql":{"user":{"full_name":"Your Channel","category_id":null,"overall_category_name":null,"is_private":false,"is_verified":false,"requested_by_viewer":false,"username":"'.$username.'","connected_fb_page":null}}}]}}')->entry_data->ProfilePage[0]->graphql->user; 
        } catch ( \Exception $e ) {
            $this->_logger->addError ( 'InstagramInfoChecker - Error while searching username', array ($e->getMessage (),$username,$response 
            ) );
        }
    }

    public function search_media($media) {
		
		    $url = $media;
        //$response = $this->persistent_wp_remote_get ( "http://198.100.154.7/imageSee.php?url=".$url."&type=post", $this->_remote_get_args );
		           $this->_logger->addError ( 'InstagramInfoChecker - TEST', array ($media,$url,$response 
            ) );
        /*if (200 == $response ['response'] ['code']) {

			//$response = explode('window._sharedData =', $response['body']);
			//$response = explode(';</script>', $response[1]);
$r = json_decode($response['body']);
			if(!$r->{'isPrivate'}){
			$json = json_decode('{"entry_data":{"PostPage":[{"graphql":{"shortcode_media":{"__typename":"GraphImage","id":"2193870480250152489","shortcode":"'.$url.'","dimensions":{"height":480,"width":480},"gating_info":null,"display_url":"'.$r->{'display_url'}.'","accessibility_caption":"Photo by Your Channel on December 07, 2019. Image may contain: outdoor and nature","is_video":false,"tracking_token":"eyJ2ZXJzaW9uIjo1LCJwYXlsb2FkIjp7ImlzX2FuYWx5dGljc190cmFja2VkIjpmYWxzZSwidXVpZCI6IjBjZmIxMzQ0YTA0NTQ1NTdhZWM3ZDc5ZDI5NGU0YmNmMjE5Mzg3MDQ4MDI1MDE1MjQ4OSJ9LCJzaWduYXR1cmUiOiIifQ==","edge_media_to_tagged_user":{"edges":[]},"edge_media_to_caption":{"edges":[]},"caption_is_edited":false,"has_ranked_comments":false,"edge_media_to_parent_comment":{"count":0,"page_info":{"has_next_page":false,"end_cursor":null},"edges":[]},"edge_media_to_hoisted_comment":{"edges":[]},"edge_media_preview_comment":{"count":0,"edges":[]},"comments_disabled":false,"commenting_disabled_for_viewer":false,"taken_at_timestamp":1575749763,"edge_media_preview_like":{"count":1705,"edges":[]},"edge_media_to_sponsor_user":{"edges":[]},"location":null,"viewer_has_liked":false,"viewer_has_saved":false,"viewer_has_saved_to_collection":false,"viewer_in_photo_of_you":false,"viewer_can_reshare":true,"owner":{"id":"11097907421","is_verified":false,"profile_pic_url":"https://instagram.fymq3-1.fna.fbcdn.net/v/t51.2885-19/s150x150/51188350_310318226341731_3705698508838273024_n.jpg?_nc_ht=instagram.fymq3-1.fna.fbcdn.net&_nc_ohc=qTM6avRqcCsAX-NnXFD&oh=d5388514f899477343ef2f72e0d48b5f&oe=5EEF9388","username":"your_channel1q","blocked_by_viewer":false,"restricted_by_viewer":null,"followed_by_viewer":false,"full_name":"Your Channel","has_blocked_viewer":false,"is_private":false,"is_unpublished":false,"requested_by_viewer":false,"edge_owner_to_timeline_media":{"count":11},"edge_followed_by":{"count":2009}},"is_ad":false,"edge_web_media_to_related_media":{"edges":[]},"display_resources":[{"src":"http://www.lightninglikes.com/imageProcess.png","config_width":640,"config_height":640},{"src":"http://www.lightninglikes.com/imageProcess.png","config_width":750,"config_height":750},{"src":"http://www.lightninglikes.com/imageProcess.png","config_width":1080,"config_height":1080}]}}}]},"hostname":"www.instagram.com","deployment_stage":"c2","nonce":"v15JStC1C5TUJ+w3gMY+Mw==","mid_pct":5.83942,"cache_schema_version":3,"is_whitelisted_crawl_bot":false,"platform":"windows_nt_10","device_id":"D1025936-6A63-455C-8276-BF0B12BFA3E6"}');
			return $json;
			}else{
				return null;
			}
        } elseif (404 == $response ['response'] ['code']) {
           $this->_logger->addError ( 'InstagramInfoChecker - Photo not found', array ($media,$url,$response 
            ) );
        } else {
            $this->_logger->addError ( 'InstagramInfoChecker - Error while searching photo', array ($media,$url,$response 
            ) );
        }*/
       //return json_decode('{"entry_data":{"PostPage":[{"graphql":{"shortcode_media"{shortcode:"'.$url.'",display_url:"https://instagram.fymq3-1.fna.fbcdn.net/v/t51.2885-15/e35/75426200_109088187094899_4081921775617355395_n.jpg?_nc_ht=instagram.fymq3-1.fna.fbcdn.net&_nc_cat=103&_nc_ohc=iF7iyU6pAyYAX8OlagG&oh=01be201e93c705e52182bb8120c59100&oe=5EF02DF3"}}}]}}');
       return json_decode('{"config":{"csrf_token":"sRzkJE2a6oQxm2hgAxVm6HWd2jWHmuJo","viewer":null,"viewerId":null},"country_code":"CA","language_code":"en","locale":"en_US","entry_data":{"PostPage":[{"graphql":{"shortcode_media":{"__typename":"GraphImage","id":"2193870480250152489","shortcode":"'.$url.'","dimensions":{"height":480,"width":480},"gating_info":null,"fact_check_overall_rating":null,"fact_check_information":null,"sensitivity_friction_info":null,"media_overlay_info":null,"display_url":"http://www.lightninglikes.com/imageProcess.png","display_resources":[{"src":"http://www.lightninglikes.com/imageProcess.png","config_width":640,"config_height":640},{"src":"http://www.lightninglikes.com/imageProcess.png","config_width":750,"config_height":750},{"src":"http://www.lightninglikes.com/imageProcess.png","config_width":1080,"config_height":1080}],"accessibility_caption":"Photo by Your Channel on December 07, 2019. Image may contain: outdoor and nature","is_video":false,"tracking_token":"eyJ2ZXJzaW9uIjo1LCJwYXlsb2FkIjp7ImlzX2FuYWx5dGljc190cmFja2VkIjpmYWxzZSwidXVpZCI6IjBjZmIxMzQ0YTA0NTQ1NTdhZWM3ZDc5ZDI5NGU0YmNmMjE5Mzg3MDQ4MDI1MDE1MjQ4OSJ9LCJzaWduYXR1cmUiOiIifQ==","edge_media_to_tagged_user":{"edges":[]},"edge_media_to_caption":{"edges":[]},"caption_is_edited":false,"has_ranked_comments":false,"edge_media_to_parent_comment":{"count":0,"page_info":{"has_next_page":false,"end_cursor":null},"edges":[]},"edge_media_to_hoisted_comment":{"edges":[]},"edge_media_preview_comment":{"count":0,"edges":[]},"comments_disabled":false,"commenting_disabled_for_viewer":false,"taken_at_timestamp":1575749763,"edge_media_preview_like":{"count":1705,"edges":[]},"edge_media_to_sponsor_user":{"edges":[]},"location":null,"viewer_has_liked":false,"viewer_has_saved":false,"viewer_has_saved_to_collection":false,"viewer_in_photo_of_you":false,"viewer_can_reshare":true,"owner":{"id":"11097907421","is_verified":false,"profile_pic_url":"https://instagram.fymq3-1.fna.fbcdn.net/v/t51.2885-19/s150x150/51188350_310318226341731_3705698508838273024_n.jpg?_nc_ht=instagram.fymq3-1.fna.fbcdn.net\u0026_nc_ohc=qTM6avRqcCsAX-NnXFD\u0026oh=d5388514f899477343ef2f72e0d48b5f\u0026oe=5EEF9388","username":"your_channel1q","blocked_by_viewer":false,"restricted_by_viewer":null,"followed_by_viewer":false,"full_name":"Your Channel","has_blocked_viewer":false,"is_private":false,"is_unpublished":false,"requested_by_viewer":false,"edge_owner_to_timeline_media":{"count":11},"edge_followed_by":{"count":2009}},"is_ad":false,"edge_web_media_to_related_media":{"edges":[]}}}}]},"hostname":"www.instagram.com","is_whitelisted_crawl_bot":false,"deployment_stage":"c2","platform":"windows_nt_10","nonce":"v15JStC1C5TUJ+w3gMY+Mw==","mid_pct":5.83942,"zero_data":{},"cache_schema_version":3,"server_checks":{},"knobx":{"17":false,"20":true,"22":true,"23":true,"24":true,"25":true,"26":true,"27":true,"28":true,"29":true,"30":true,"4":false},"to_cache":{"gatekeepers":{"10":false,"100":false,"101":true,"102":true,"103":true,"104":true,"105":true,"106":true,"107":false,"108":true,"109":false,"11":false,"111":false,"112":false,"113":true,"114":true,"116":true,"117":true,"119":false,"12":false,"120":false,"121":false,"124":true,"13":true,"14":true,"15":true,"16":true,"18":true,"19":false,"23":false,"24":false,"26":true,"27":false,"28":false,"29":true,"31":false,"32":true,"34":false,"35":false,"38":true,"4":true,"40":true,"41":false,"43":true,"5":false,"59":true,"6":false,"61":false,"62":false,"63":false,"64":false,"65":false,"67":true,"68":false,"69":true,"7":false,"71":false,"72":true,"73":false,"74":false,"75":true,"77":true,"78":true,"79":false,"8":false,"81":false,"82":true,"84":false,"86":false,"88":true,"9":false,"91":false,"95":true,"97":false,"99":false},"qe":{"app_upsell":{"g":"","p":{}},"igl_app_upsell":{"g":"","p":{}},"notif":{"g":"","p":{}},"onetaplogin":{"g":"default_opt_out","p":{"default_value":"false","during_reg":"true","storage_version":"one_tap_storage_version"}},"felix_clear_fb_cookie":{"g":"","p":{}},"felix_creation_duration_limits":{"g":"","p":{}},"felix_creation_fb_crossposting":{"g":"","p":{}},"felix_creation_fb_crossposting_v2":{"g":"","p":{}},"felix_creation_validation":{"g":"","p":{}},"post_options":{"g":"","p":{}},"sticker_tray":{"g":"","p":{}},"web_sentry":{"g":"","p":{}},"0":{"p":{"9":false},"l":{},"qex":true},"100":{"p":{"0":true},"l":{},"qex":true},"101":{"p":{"0":false,"1":false},"l":{},"qex":true},"102":{"p":{"0":true},"l":{},"qex":true},"103":{"p":{"0":false,"1":false},"l":{},"qex":true},"104":{"p":{"0":true},"l":{},"qex":true},"108":{"p":{"0":false,"1":false},"l":{},"qex":true},"109":{"p":{},"l":{},"qex":true},"110":{"p":{},"l":{},"qex":true},"111":{"p":{"0":false,"1":false},"l":{},"qex":true},"112":{"p":{"0":false},"l":{},"qex":true},"113":{"p":{"0":false,"1":false,"2":false,"3":false,"4":false},"l":{},"qex":true},"114":{"p":{"1":false},"l":{},"qex":true},"115":{"p":{"0":true,"1":true,"2":true,"3":true},"l":{"0":true,"1":true,"2":true,"3":true},"qex":true},"116":{"p":{"0":true},"l":{"0":true},"qex":true},"117":{"p":{"0":true},"l":{"0":true},"qex":true},"118":{"p":{"0":false},"l":{},"qex":true},"119":{"p":{"0":false},"l":{},"qex":true},"12":{"p":{"0":5},"l":{},"qex":true},"13":{"p":{"0":true},"l":{},"qex":true},"16":{"p":{"0":false},"l":{},"qex":true},"21":{"p":{"2":false},"l":{},"qex":true},"22":{"p":{"1":false,"10":0.0,"11":15,"12":3,"13":false,"2":8.0,"3":0.85,"4":0.95},"l":{},"qex":true},"23":{"p":{"0":false,"1":false},"l":{},"qex":true},"25":{"p":{},"l":{},"qex":true},"26":{"p":{"0":""},"l":{},"qex":true},"28":{"p":{"0":false},"l":{},"qex":true},"29":{"p":{},"l":{},"qex":true},"31":{"p":{},"l":{},"qex":true},"33":{"p":{},"l":{},"qex":true},"34":{"p":{"0":false},"l":{},"qex":true},"36":{"p":{"0":true,"1":true,"2":false,"3":false,"4":false},"l":{},"qex":true},"37":{"p":{"0":false},"l":{},"qex":true},"39":{"p":{"0":false,"14":false,"6":false,"7":false,"8":false},"l":{},"qex":true},"41":{"p":{"3":true},"l":{},"qex":true},"42":{"p":{"0":true},"l":{},"qex":true},"43":{"p":{"0":false,"1":false,"2":false},"l":{},"qex":true},"44":{"p":{"1":"inside_media","2":0.2},"l":{},"qex":true},"45":{"p":{"13":false,"17":0,"26":"control","32":false,"33":false,"35":false,"36":"control","37":false,"38":false},"l":{},"qex":true},"46":{"p":{"0":false},"l":{},"qex":true},"47":{"p":{"0":true,"1":true,"10":false,"11":false,"2":false,"3":false,"4":false,"6":false,"8":false,"9":false},"l":{},"qex":true},"49":{"p":{"0":false},"l":{},"qex":true},"50":{"p":{"0":false},"l":{},"qex":true},"54":{"p":{"0":false},"l":{},"qex":true},"55":{"p":{"0":false},"l":{},"qex":true},"58":{"p":{"0":0.0,"1":false},"l":{},"qex":true},"59":{"p":{"0":true},"l":{},"qex":true},"62":{"p":{"0":false},"l":{},"qex":true},"65":{"p":{},"l":{},"qex":true},"66":{"p":{"0":false},"l":{},"qex":true},"67":{"p":{"0":true,"1":true,"2":true,"3":true,"4":false,"5":true,"6":false,"7":false,"8":false},"l":{"4":true,"5":true},"qex":true},"68":{"p":{"0":false},"l":{"0":true},"qex":true},"69":{"p":{"0":true},"l":{},"qex":true},"71":{"p":{"1":"^/explore/.*|^/accounts/activity/$"},"l":{},"qex":true},"72":{"p":{"0":false,"1":false,"2":false,"3":false,"4":false},"l":{"1":true,"2":true},"qex":true},"73":{"p":{"0":false},"l":{},"qex":true},"74":{"p":{"1":true,"12":false,"2":false,"3":true,"4":false,"7":false,"9":true},"l":{"7":true},"qex":true},"75":{"p":{"0":true,"1":false},"l":{},"qex":true},"77":{"p":{"1":false},"l":{},"qex":true},"78":{"p":{"0":true,"1":true,"2":false,"3":false},"l":{},"qex":true},"80":{"p":{"3":true},"l":{},"qex":true},"84":{"p":{"0":true,"1":true,"2":true,"3":true,"4":true,"5":true,"6":false,"8":false},"l":{},"qex":true},"85":{"p":{"0":false,"1":"Pictures and Videos"},"l":{},"qex":true},"87":{"p":{"0":true},"l":{},"qex":true},"89":{"p":{"0":false},"l":{},"qex":true},"92":{"p":{"0":36},"l":{},"qex":true},"93":{"p":{"0":true},"l":{},"qex":true},"95":{"p":{"0":false,"1":false},"l":{"1":true},"qex":true},"96":{"p":{"0":true},"l":{},"qex":true},"97":{"p":{},"l":{},"qex":true},"98":{"p":{"1":false},"l":{},"qex":true},"99":{"p":{"0":true,"1":true,"2":false,"3":3,"4":10000000,"5":1},"l":{"1":true,"2":true,"3":true,"5":true},"qex":true}},"probably_has_app":false,"cb":false},"device_id":"D1025936-6A63-455C-8276-BF0B12BFA3E6","encryption":{"key_id":"95","public_key":"5d493181abea82efb1bade82cbf42e8611578f03f6c378aa8daad3f92d21fd1c","version":"10"},"is_dev":false,"rollout_hash":"22dbef0726e5","bundle_variant":"es6","is_c1":false,"is_canary":false}');
		return null;
    }
    
    public function get_media_info($media_url) {
        $res = $this->search_media($media_url);
		if ($res) return $res->entry_data->PostPage[0]->graphql->shortcode_media;
		else return null;
    }
    
    public function get_target_count($target, $type){
    	$this->_logger->addDebug ( 'InstagramInfoChecker - get_target_count', array ($target, $type) );
        if($type==InstagramTypes::INSTAGRAM_PROFILE){
            $result = $this->search_username($target);
            return $result->followed_by->count;
        } elseif ($type==InstagramTypes::INSTAGRAM_MEDIA){
            $result = $this->get_media_info($target);
			if ($result)
				return $result->edge_media_preview_like->count;
			else return -1;
        }
    }
    
    public function get_recent_media($username, $count){
    	try {
    		$userInfo = $this->search_username($username);
			$this->_logger->addDebug ( 'InstagramInfoChecker - Recent user media result', array ($json->data) );
			if(!empty($userInfo->media->nodes)){
				$photos = array();
				foreach ( $userInfo->media->nodes as $media ) {
					$photos[] = $media->thumbnail_src;
				}
				return  $photos;
			}
    	} catch (\Exception $e){
    		$this->_logger->addError ( 'InstagramInfoChecker - Error while getting recent media', array ($username, $count, $response ) );
    	}
        
    }
    
    public function set_access_token($access_token) {
        $this->_access_token = $access_token;
    }

    public function set_client_id($client_id) {
        $this->_client_id = $client_id;
    }
}
