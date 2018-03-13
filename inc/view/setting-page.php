<div id="inkmember_wrap">
    <div class="member_head"><div id="icon-options-general" class="icon32">	
        </div>
        <h2><?php _e('InkMember Settings', 'inkmember'); ?></h2></div>
    <div id="member_form">
        <form method="post" id="product_form" action="">
            <table>
                <tbody> 
                    <tr>
                        <td class="label"><label for="payment_mode"><?php _e('Payment Mode', 'inkmember'); ?></label></td>
                        <td>
                            <select name="payment_mode" id="payment_mode">
                                <?php
                                $payment_mode = get_option('im_payment_mde');
                                ?>
                                <option <?php if ($payment_mode == 'paypal' || $payment_mode == '') echo 'selected="paypal"'; ?>  value="paypal">Paypal</option>
                                <option <?php if ($payment_mode == 'cashlater') echo 'selected="cashlater"'; ?> value="cashlater"><?php _e('Cash Later', 'inkmember'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr class="mode_cash">
                        <td class="label"><label for="mode_cash"><?php _e('Cash Form Url:', 'inkmember'); ?></label>
                            <a class="tooltip" title="<?php _e('Enter your url to getting details from suscribers for their payment via bank or cash.', 'inkmember'); ?>" href="#"></a>
                        </td>
                        <td>
                            <input type="text" name="pay_cash_url" id="mode_cash" value="<?php echo get_option('im_pay_cash_url'); ?>"/>
                        </td>
                    </tr>            
                    <tr class="mode_pay">
                        <td class="label">
                            <label for="paypal_email"><?php _e('Paypal Email:', 'inkmember'); ?></label>
                            <a class="tooltip" title="<?php _e('Your Paypal Email for receiving payments. Eg: payments@yoursite.com', 'inkmember'); ?>" href="#"></a>
                        </td>
                        <td>
                            <input type="text" name="paypal_email" id="paypal_email" value="<?php echo get_option('im_paypal_email'); ?>"/>
                        </td>
                    </tr>       
                    <tr class="mode_pay">
                        <td class="label">
                            <label for="paypal_locale"><?php _e('PayPal Locale:', 'inkmember'); ?></label>
                            <a class="tooltip" title="<?php _e("Select PayPal locale if you want to redirect to particular country's language.", 'inkmember'); ?>" href="#"></a>
                        </td>
                        <td>
                            <?php $im_paypal_locale = get_option('im_paypal_locale'); ?>
                            <select name="paypal_locale" id="paypal_locale">
                                <option <?php if ($im_paypal_locale == 'au') echo 'selected="selected"'; ?> value="au"><?php _e('Australia', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'at') echo 'selected="selected"'; ?>value="at"><?php _e('Austria', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'be') echo 'selected="selected"'; ?>value="be"><?php _e('Belgium', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'br') echo 'selected="selected"'; ?>value="br"><?php _e('Brazil', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'ca') echo 'selected="selected"'; ?>value="ca"><?php _e('Canada', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'ch') echo 'selected="selected"'; ?>value="ch"><?php _e('Switzerland', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'cn') echo 'selected="selected"'; ?>value="cn"><?php _e('China', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'de') echo 'selected="selected"'; ?>value="de"><?php _e('Germany', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'es') echo 'selected="selected"'; ?>value="es"><?php _e('Spain', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'gb') echo 'selected="selected"'; ?>value="gb"><?php _e('United Kingdom', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'fr') echo 'selected="selected"'; ?>value="fr"><?php _e('France', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'it') echo 'selected="selected"'; ?>value="it"><?php _e('Italy', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'nl') echo 'selected="selected"'; ?>value="nl"><?php _e('Netherlands', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'pl') echo 'selected="selected"'; ?>value="pl"><?php _e('Poland', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'pt') echo 'selected="selected"'; ?>value="pt"><?php _e('Portugal', 'inkmember'); ?></option>
                                <option <?php if ($im_paypal_locale == 'ru') echo 'selected="selected"'; ?>value="ru"><?php _e('Russia', 'inkmember'); ?></option>
                                <option <?php if (!$im_paypal_locale || $im_paypal_locale == 'us') echo 'selected="selected"'; ?>value="us"><?php _e('United States', 'inkmember'); ?></option>
                            </select>
                        </td>
                    </tr> 
                    <tr class="mode_pay">
                        <td class="label">
                            <label for="sandbox"><?php _e('Sandbox Mode:', 'inkmember'); ?></label>
                            <a class="tooltip" title="<?php _e('Choose Yes if you are in testing phase through Paypal Sandbox Account. Else Choose No for real Paypal Transactions.', 'inkmember'); ?>" href="#"></a>
                        </td>
                        <td>
                            <select id="sandbox" name="sandbox_mode"> 
                                <?php
                                $sandbox = get_option('im_sabdbox_mode');
                                ?>
                                <option <?php if ($sandbox == false) echo 'selected="selected"'; ?> value="0"><?php echo esc_attr(__('No', 'inkmember')); ?></option> 
                                <option <?php if ($sandbox == true) echo 'selected="selected"'; ?>  value="1"><?php echo esc_attr(__('Yes', 'inkmember')); ?></option> 
                            </select>
                        </td>
                    </tr>
                    <tr class="mode_pay">
                        <td>
                            <label for="return_member_page"><?php _e('Thanks Page:', 'inkmember'); ?></label>
                            <a class="tooltip" title="<?php _e('Choose your thank you page. This page will displayed when user redirects to your site after made payment from paypal.', 'inkmember'); ?>" href="#"></a>
                        </td>
                        <td>
                            <select id="return_member_page" name="return_member_page"> 
                                <option value=""><?php echo esc_attr(__('Select One')); ?></option> 
                                <?php
                                $return_page = get_option('im_return_page');
                                $return_pages = get_pages();
                                foreach ($return_pages as $page) {
                                    if ($return_page == get_page_link($page->ID)) {
                                        $selected = 'selected="selected"';
                                    } else {
                                        $selected = '';
                                    }
                                    $option = '<option ' . $selected . ' value="' . get_page_link($page->ID) . '">';
                                    $option .= $page->post_title;
                                    $option .= '</option>';
                                    echo $option;
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr class="captcha_setting">
                        <td>
                            <label for="im_recaptcha_setting"><?php _e('Captcha On/Off', 'inkmember'); ?></label>
                            <a class="tooltip" title="<?php _e('By default captcha is deactivated on user registration, turn it on to activate.', 'inkmember'); ?>" href="#"></a>
                        </td>
                        <td>
                            <?php
                            $is_captcha_on = get_option('im_recaptcha');
                            ?>
                            <select id="im_recaptcha_setting" name="im_recaptcha_setting"> 
                                <option <?php if ($is_captcha_on === 'on') echo 'selected="selected"'; ?> value="on"><?php echo esc_attr(__('On', 'inkmember')); ?></option> 
                                <option <?php if ($is_captcha_on === 'off') echo 'selected="selected"'; ?>  value="off"><?php echo esc_attr(__('Off', 'inkmember')); ?></option> 
                            </select>
                        </td>
                    </tr>
                    <tr class="captcha_publickey_setting">
                        <td>
                            <label for="im_recaptcha_public_setting"><?php _e('Recaptcha Public Key', 'inkmember'); ?></label>
                            <a class="tooltip" title="<?php _e('Go to Google Recaptcha to Create your Public key', 'inkmember'); ?>" href="#"></a>
                        </td>
                        <td>
                            <?php
                            $publickey = get_option('im_recaptcha_public');
                            ?>
                            <input type="text" id="im_recaptcha_public_setting" name="im_recaptcha_public_setting" value="<?php echo $publickey; ?>">
                        </td>
                    </tr>
                    <tr class="captcha_privatekey_setting">
                        <td>
                            <label for="im_recaptcha_private_setting"><?php _e('Recaptcha Private Key', 'inkmember'); ?></label>
                            <a class="tooltip" title="<?php _e('Go to Google Recaptcha to Create your Private key', 'inkmember'); ?>" href="#"></a>
                        </td>
                        <td>
                            <?php
                            $secret_keys = get_option('im_recaptcha_private');
                            ?>
                            <input type="text" id="im_recaptcha_private_setting" name="im_recaptcha_private_setting" value="<?php echo $secret_keys; ?>">
                        </td>
                    </tr>
                </tbody>
            </table>
            <input type="submit" name="submit" value="<?php _e('Save', 'inkmember'); ?>" class="button-primary" />
        </form>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var mode_val = jQuery('#payment_mode').val();
        if (mode_val === 'paypal') {
            jQuery('.mode_cash').css('display', 'none');
        } else {
            jQuery('.mode_pay').css('display', 'none');
        }
        jQuery('#payment_mode').change(function () {
            var mode_type = jQuery(this).val();
            if (mode_type === 'cashlater') {
                jQuery('.mode_pay').css('display', 'none');
                jQuery('.mode_cash').css('display', 'table-row');
            } else {
                jQuery('.mode_pay').css('display', 'table-row');
                jQuery('.mode_cash').css('display', 'none');
            }
        });
    });
</script>