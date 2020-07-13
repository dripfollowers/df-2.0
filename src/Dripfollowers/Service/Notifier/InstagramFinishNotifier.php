<?php

namespace DripFollowers\Service\Notifier;

class InstagramFinishNotifier extends InstagramServiceNotifier {

    function get_subject() {
        return 'Your FluidBuzz.com Order is Complete';
    }

    function get_email_body() {
        ob_start();
        ?>
        <table class="body-wrap" style=" width:100%;  font-family: 'Poppins', sans-serif;">
            <tr>
                <td></td>
                <td align="center">
                    <table style="background-color: #ffffff; padding:25px; width:90%;">
                        <tr>
                                <td><img src="https://fluidbuzz.com/wp-content/uploads/2019/09/checkmark.jpg" style="display: block; margin: 20px auto;max-width:100%;width:150px;"/></td>
                        </tr>
                        <?php 
                        if ($this->_pack->get_type () == PacksTypes::Instant_Likes||$this->_pack->get_type () == PacksTypes::Instant_Views){
                            $target_link = $this->_extraInfo ['target'];
                        } else {
                             $target_link = 'https://instagram.com/'.$this->_extraInfo ['target'];
                        }

                        if ($this->_pack->get_type () == PacksTypes::Instant_Followers) {
                            $message = 'Want a Natural Stream of IG Followers Hitting Your Account Daily?';
                            $cta = 'Checkout our Automatic Followers Now!';
                        } else if ($this->_pack->get_type () == PacksTypes::Instant_Likes ){
                            $message = 'Tired of checking out every time you upload?';
                            $cta = 'Go Automatic to Save both Time & Money!';
                        } else if ($this->_pack->get_type () == PacksTypes::Instant_Views ){
                            $message = 'Tired of checking out every time you upload?';
                            $cta = 'Go Automatic to Save both Time & Money!';
                        } else {
                            $message = '';
                            $cta = 'See our latest packages!';
                        }
                        ?>
                        <tr>
                                <td>   
                                        <h2 style="color: #333; font-size: 20px;text-align:center;font-weight:400;margin-bottom:0;">Thank you for to grow your instagram with FluidBuzz.</h2>
                                        <h1 style="margin-bottom: 15px; margin-top:10px;margin-bottom:25px;font-size:22px; color:#000; text-align: center;">Congrats! Your <a href="<?php echo $target_link; ?>">order</a> is complete.</h1>
                                        <p style="margin: 20px 0 10px;text-align:center;color:#444;font-size:16px;"><?php echo $message; ?></p>
                                </td>
                        </tr>
                        <tr>
                            <td>
                                    <div style="background: #ff6766;
                                                background: -moz-linear-gradient(0deg, #ff6766 0%, #ff995b 100%);
                                                background: -webkit-linear-gradient(0deg, #ff6766 0%, #ff995b 100%);
                                                background: -ms-linear-gradient(0deg, #ff6766 0%, #ff995b 100%);
                                                text-align:center;color:white;padding:30px;margin-top:20px;">
                                            <p style="font-size:17px;margin:0 0 15px;"><?php echo $cta; ?></p>
                                            <a href="#/" style="font-size:17px;display:inline-block;color:white;background:rgba(255,255,255,.3);padding:11px 40px;border-radius:25px;text-decoration:none;text-transform:uppercase;">Learn More</a>
                                    </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p style="font-size:12px; color: #666;margin-top:20px;text-align:center;">If you have any questions or concerns please email us at <a href="mailto:support@fluidbuzz.com">support@fluidbuzz.com</a></p>
                            </td>
                        </tr>
                    </table>
                </td>
                <td></td>
            </tr>

        </table>
    <?php
        $content = ob_get_clean();
        return $content;
    }
}