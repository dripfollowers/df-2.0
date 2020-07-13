<?php

namespace DripFollowers\ShortCode;

use DripFollowers\DripFollowers;

class ServiceDownMsg {
    private $_plugin;

    public function __construct(DripFollowers $plugin) {
        $this->_plugin = $plugin;
    }

    public function render() {
        if ($this->_plugin->is_service_down ()) {
            ?>
        <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var msgClosed = 'service-down-msg-closed';
            var msgSlided = 'service-down-msg-slided';
            if($.cookie(msgClosed)!=undefined && $.cookie(closeCookie)==msgClosed ){
            	$('#service-down').hide();
            } else {
                if($.cookie(msgSlided)==msgSlided){
                	$('#service-down').show();
                } else {
            	   $('#service-down').slideDown('slow');
            	   var date = new Date();
                   date.setTime(date.getTime() + (30 * 60 * 1000));
                   $.cookie(msgSlided, msgSlided, { expires: date, path: '/' });
                }
            }
            $('#service-down .close').click(function(){
            	var date = new Date();
            	date.setTime(date.getTime() + (12 * 60 * 60 * 1000));
            	$.cookie(msgClosed, msgClosed, { expires: date, path: '/' });
            });
        });
        </script>
<div id="service-down" data-alert
	class="alert-box warning text-center top-msg" style="display: none;">
            <?php echo $this->_plugin->get_setting('service-down-message'); ?>
            <a href="#" class="close">&times;</a>
</div>
<?php
        }
    }
}