<?php

namespace DripFollowers\Service\Notifier;

use DripFollowers\Common\PacksTypes;

class InstagramStartNotifier extends InstagramServiceNotifier {

    function get_subject() {
        return 'Your FluidBuzz Order Has Started!';
    }
    
    function get_email_body(){
        ob_start();
        ?>

        <!-- BODY (start notifier) -->
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
                        ?>
                        <tr>
                                <td>   
                                        <h2 style="color: #333; font-size: 20px;text-align:center;font-weight:400;margin-bottom:0;">Thank you for to grow your instagram with FluidBuzz.</h2>
                                        <h1 style="margin-bottom: 15px; margin-top:10px;margin-bottom:25px;font-size:22px; color:#000; text-align: center;">Your <a href="<?php echo $target_link; ?>">order</a> is Confirmed</h1>
                                </td>
                        </tr>
                        <tr>
                            <td>
                                <!-- PRICING TABLE -->
                                <?php
                                if ($this->_pack->get_type () == PacksTypes::Automatic_Followers) {
                                    $subject = $this->_pack->get_base_number().' followers per month';
                                    $message = 'Your followers will start coming in shortly. You will receive '.$this->_pack->get_base_number().' Automatic Followers per month until your subscription is canceled.';
                                } else if ($this->_pack->get_type () == PacksTypes::Automatic_Likes ){
                                    $subject = $this->_pack->get_base_number().' likes per post. (max 3 posts per day)';
                                    $message = 'You will receive '.$this->_pack->get_base_number().' likes on each of your future posts (up to 3 posts / day) until your subscription is canceled. Videos will receive both likes and views.';
                                } else {
                                    $subject = $this->_pack->get_base_number().' '.$this->_pack->get_subject();
                                    $message = 'You will receive your '.$this->_pack->get_subject().' shortly.';
                                }

                                ?>
                                <table style="width:100%;font-size:14px;border-collapse: collapse;border-color: #ccc;">
                                    <tbody>
                                        <tr>
                                            <th style="width:75%;text-align:left;text-transform:uppercase;padding:20px 15px;border:1px solid #cccccc;">Product
                                            </th>
                                            <th style="width:25%;text-align:left;text-transform:uppercase;padding-left:15px;border:1px solid #cccccc;">Price
                                            </th>
                                        </tr>
                                        <tr>
                                            <td style="width:75%;padding:20px 15px;border:1px solid #cccccc;">
                                                <?php echo $subject; ?>
                                            </td>
                                            <td style="width:25%;padding-left:15px;border:1px solid #cccccc;">
                                                $<?php echo $this->paymentamount; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p style="font-size:12px; color: #666;margin-top:20px;text-align:center;"><?php echo $message; ?></p>
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