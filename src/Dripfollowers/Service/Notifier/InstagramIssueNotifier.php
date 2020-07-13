<?php

namespace DripFollowers\Service\Notifier;

class InstagramIssueNotifier extends InstagramServiceNotifier {

    function get_subject() {
        return 'FluidBuzz Campaign Progress: Delay Issue';
    }

    function get_email_body() {
        ob_start();
        ?>
        <!-- BODY -->
        <table class="body-wrap" style="width:100%;  font-family: 'Poppins', sans-serif;">
            <tr>
                <td></td>
                <td align="center">
                        <table style=" background-color: #ffffff; padding:25px;width:90%;">
                            <tr>
                                <td>
                                    
                                    <h1 style="margin-bottom: 15px; font-size:22px; color:#444; text-align: center;">Campaign Progress: <span style="color: #111;">Delay Issue</span></h1>
                                    <p style="font-size:14px; text-align: center; margin-bottom:50px;">
                                    We are encountering some delay problems for your campaign <?php echo $this->get_target_as_link(); ?>.
                                    <br />
                                    We do our best to fix this problem... Just don't worry you will be notified with your compain completion as soon as possible.
                                    </p>

                                    <h2 style="color: #666; font-size: 18px;">Issues?</h2>
                                    <p style="font-size:14px; color: #666;">If you have any questions or concerns please email us at <a href="mailto:support@fluidbuzz.com">support@fluidbuzz.com</a></p>

                                </td>
                            </tr>
                        </table>


                </td>
                <td></td>
            </tr>
        </table><!-- /BODY -->
    <?php
        $content = ob_get_clean();
        return $content;
    }
}