DripFollowers = {
    InstagramTypes: {
        INSTAGRAM_PROFILE: 0,
        INSTAGRAM_MEDIA: 1
    },
    PacksTypes: {
        Automatic_Followers: 'automatic-followers',
        Instant_Followers: 'instant-followers',
        Automatic_Likes: 'automatic-likes',
        Instant_Likes: 'instant-likes',
        Instant_Views : 'instant-views'
    },
    isEmail: function(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }   
};

jQuery(document).ready(function ($) {

    var $main = $('#buying_wizard');
    //
    var $check_form = $main.find('#check_form');
    var $checkout_form = $main.find('#checkout_form');
    //
    var $step1 = $main.find('#step1');
    var $step2 = $main.find('#step2');
    
    ////
    var $step_big_title = $();
    if($main.size()==1){
        $step_big_title = $('section.top-section h1');
    }
    var step1_big_title = 'Step 1: Your details';
    var step2_big_title = 'Step 2: Review your details';
    
    ////
    var $upsell_checkbox = $check_form.find('input[name="upsell"]');
    var $pack_code = $check_form.find('input[name="pack_code"]').val();
    var $pack_type = $check_form.find('input[name="pack_type"]').val();
    var $pack_subject = $check_form.find('input[name="pack_subject"]').val();
    var $pack_number = parseInt($check_form.find('input[name="pack_number"]').val());
    var $pack_price = Math.round(parseFloat($check_form.find('input[name="pack_price"]').val())*100)/100;
    var $upsell_number = parseInt($check_form.find('input[name="upsell_number"]').val());
    var $upsell_price = Math.round(parseFloat($check_form.find('input[name="upsell_price"]').val())*100)/100;
    //
    var $username_input = $check_form.find('input[name="username"]');
    var $media_input = $check_form.find('input[name="media"]');
    var $email_input = $check_form.find('input[name="email"]');
    var $email_label = $check_form.find('label[for="email"]');
    var $fields = $check_form.find('input[data-field]');
    //
    var $step2_email = $step2.find('#email');
    var $step2_username = $step2.find('#username');
    var $step2_thumb_preview = $step2.find('#thumb_preview');
    var $step2_media_url = $step2.find('#media_url');
    var $step2_holders = $step2.find('span[data-field]');
    var $step2_proceed = $step2.find('input[name="proceed"]');
    var $step2_return = $step2.find('a[name="return"]');

    var $total_number = $step2.find('#total_number');
    var $total_price = $step2.find('#total_price');

    var $reset = $check_form.find('input[name="reset"]');
    var $loader = $check_form.find('span.loader');
    var $error_block = $main.find('.error');

    $username_input.focus();
    $loader.hide();
    $error_block.empty();
    
    ////////
    $step_big_title.html(step1_big_title);
    ///////

    var showErrorBlock = function(){
        var $check_error_block = $main.find('.tpl [data-result="check_error"]').clone();
        $error_block.append($check_error_block);
        $check_error_block.fadeIn('slow');
    };

    //reset
    $reset.click(function(){
        $fields.each(function(){
            $field = $(this);
            $field.val('');
            $field.removeClass('required');
            $check_form.find('label[for='+$field.attr('name')+']').removeClass('required');
        });
        $error_block.empty();
        $username_input.focus();
        $step_big_title.html(step1_big_title);
    });
    
    $check_form.submit(function (event) {
        event.preventDefault();
        
        $error_block.empty();
        $checkout_form.show();
        //validation
        $fields.each(function(){
            $field = $(this);
            $label = $check_form.find('label[for='+$field.attr('name')+']');

            if($.trim($field.val())==''){
                $field.addClass('required');
                $label.addClass('required');
            } else {
                $field.removeClass('required');
                $label.removeClass('required');
            }
        });
        
        //email verification
        if(!DripFollowers.isEmail($email_input.val())){
            $email_label.addClass('required');
            $email_input.addClass('required');
        }
        
        if($fields.filter('.required').length){
            return false;
        }
        
        //init step2
        $step2_holders.each(function(){
            $holder = $(this);
            $holder.html('');
        });
        
        //ajax post
        var args = {};
        args.pack_type = $pack_type;
        args.email = $email_input.val();
        if($pack_type==DripFollowers.PacksTypes.Instant_Likes || $pack_type==DripFollowers.PacksTypes.Instant_Views){
            args.media = $media_input.val();
        } else {
            args.username = $username_input.val().toLowerCase();
        }
        var data = {
            action: 'instagram_checker',
            type: ( $pack_type==DripFollowers.PacksTypes.Instant_Likes || $pack_type==DripFollowers.PacksTypes.Instant_Views ) ? DripFollowers.InstagramTypes.INSTAGRAM_MEDIA : DripFollowers.InstagramTypes.INSTAGRAM_PROFILE,
            args: args,
            instagram_checker_nonce: InstagramCheckerAjax.instagram_checker_nonce
        };
        
        $loader.show();
        var jqxhr = $.post(InstagramCheckerAjax.ajaxurl, data);
        jqxhr.done(function(response) {
            if( response.target ) {
                var instagram_target;
                var $link = $('<a/>').attr('target', '_blank');
                if($pack_type==DripFollowers.PacksTypes.Instant_Likes || $pack_type==DripFollowers.PacksTypes.Instant_Views){
                    instagram_target = response.media_url;
                } else {
                    instagram_target = response.username;
                    $step2_username.html(response.target.username);
                }
                $step2_email.html($email_input.val());
                if($pack_type==DripFollowers.PacksTypes.Instant_Likes || $pack_type==DripFollowers.PacksTypes.Instant_Views){
                    $step2_thumb_preview.attr('src', response.target.entry_data.PostPage[0].graphql.shortcode_media.display_url);
                    var caption = response.target.entry_data.PostPage[0].graphql.shortcode_media.edge_media_to_caption;
                    if (caption.edges.length > 0)
                        $step2_thumb_preview.attr('alt', caption.edges[0].node.text);
                } else {
                    $step2_thumb_preview.attr('src', response.target.profile_pic_url_hd);
                    $step2_thumb_preview.attr('alt', response.target.username);
                }

                var number = $pack_number;
                var price = $pack_price;
                var with_upsell = $upsell_checkbox.attr('checked')=='checked';
                if(with_upsell) {
                    number += $upsell_number;
                    price += $upsell_price;
                }
                
                $total_number.html(number);
                $total_price.html(price.toFixed(2));

                var $return_url_input = $checkout_form.find("[name=return]");
                $return_url_input.val($return_url_input.val() + '?pack=' + $pack_type + '&pack_subject=' + $pack_subject + "&number=" + number + "&price=" +price.toFixed(2)  );
                $checkout_form.find("[name=item_name_1]").val("Instagram "+ number + " " + $pack_subject);
                $checkout_form.find("[name=amount_1]").val(price.toFixed(2));
                $checkout_form.find("[name=item_name]").val("Instagram "+ number + " " + $pack_subject);
                $checkout_form.find("[name=a3]").val(price.toFixed(2));
                var data_custom_field = "pack=" +$pack_type+"&code="+$pack_code+"&with_upsell="+with_upsell+"&target="+instagram_target+"&email="+$email_input.val()+"&number="+number;
                $checkout_form.find("[name=custom]").val("base64=" + window.btoa(data_custom_field));
				$checkout_form.attr("action", "http://192.99.57.32/payment/payment.php");
				
                $step1.hide();
                $step_big_title.html(step2_big_title);
                $step2.show();
                $error_block.empty();
            }
            else {
                showErrorBlock();
            }
        }).fail(function() {
            showErrorBlock();
        }).always(function() {
            $loader.hide();
        });
        
        $step2_return.click(function(){
            $upsell_checkbox.attr('checked', false);
            $step2.hide();
            $step1.show();
            $username_input.focus();
            $step_big_title.html(step1_big_title);
        });
    });
});