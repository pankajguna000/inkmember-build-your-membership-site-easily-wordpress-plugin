<div id="member_form">
    <form method="post" id="product_form" action="">
        <table>
            <tbody>
                <tr>
                    <td class="label">
                        <label for="product_name"><?php _e('Product Name:', 'inkmember'); ?></label>
                        <a class="tooltip" title="<?php _e('Name of Your Product will come here. Eg: Yoga Series.', 'inkmember'); ?>" href="#"></a>
                    </td>
                    <td>
                        <input type="text" name="product_name" id="product_name" value="<?php echo $results->product_name; ?>"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label><?php _e('Billing Option:', 'inkpage'); ?></label>
                        <a class="tooltip" title="<?php _e('Choose whether you want to offer product for One Time Payment or Recurring Payment.', 'inkmember'); ?>" href="#"></a>
                    </td>
                    <td>
                        <input type="radio" <?php if ($results->billing_option == 'one_time') echo 'checked="checked"'; ?> id="one_time" name="billing_option" value="one_time"/>&nbsp;<?php _e('One Time Purchase or', 'inkmember'); ?>&nbsp;&nbsp;&nbsp;
                        <input  type="radio" id="recurring" <?php if ($results->billing_option == 'recurring') echo 'checked="checked"'; ?> name="billing_option" value="recurring"/>&nbsp;<?php _e('Recurring Subscription', 'inkmember'); ?>
                    </td>
                </tr>
    <!--            <tr class="payment_putton"><td><label for="payment_button">Payment Button Image(Optional):</label><a class="tooltip" title="" href="#"></a></td><td><input type="text" name="payment_button" id="payment_button" value="<?php echo $results->p_button_img; ?>"/></td></tr>-->
                <tr class="currency">
                    <td class="label">
                        <label for="currency"><?php _e('Currency:', 'inkmember'); ?></label>
                        <a class="tooltip" title="<?php _e('Choose Your Payment Currency.', 'inkmember'); ?>" href="#"></a>
                    </td>
                    <td>
                        <select name="currency" id="currency">
                            <?php
                            $pcurrency = $results->currency;
                            $currencys = im_currency();
                            foreach ($currencys as $key => $currency) {
                                if ($pcurrency == $key) {
                                    $selected = 'selected="selected"';
                                } else {
                                    $selected = '';
                                }
                                echo '<option ' . $selected . ' value="' . $key . '">', $currency . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr class="product_price">
                    <td class="label">
                        <label class="product_price_label" for="product_price"><?php _e('Product Price:', 'inkmember'); ?></label>
                        <a class="tooltip tip_onetime" title="<?php _e('Enter Pricing for your product.', 'inkmember'); ?>" href="#"></a>
                        <a class="tooltip tip_recurring" title="<?php _e('This is the amount to be charged for the first time payment.', 'inkmember'); ?>" href="#"></a>
                    </td>
                    <td>
                        <input type="text" class="small" name="product_price" id="product_price" value="<?php echo $results->product_price; ?>"/>
                    </td>
                </tr>
                <tr class="payment p_period">
                    <td class="label">
                        <label for="payment_period"><?php _e('Payment Period:', 'inkmember'); ?></label>
                        <a class="tooltip" title="<?php _e('Time period of first payment after which the second price will be charged.', 'inkmember'); ?>" href="#"></a>
                    </td>
                    <td>
                        <input class="small" type="text" name="payment_period" id="payment_period" value="<?php echo $results->payment_period; ?>"/>&nbsp;
                        <select name="payment_period_cycle" id="payment_period_cycle">
                            <option <?php if ($results->payment_period_cycle == 'D') echo 'selected="selected"'; ?> value="D"><?php _e('day(s)', 'inkmember'); ?></option>
                            <option <?php if ($results->payment_period_cycle == 'W') echo 'selected="selected"'; ?> value="W"><?php _e('week(s)', 'inkmember'); ?></option>
                            <option <?php if ($results->payment_period_cycle == 'M') echo 'selected="selected"'; ?> value="M"><?php _e('month(s)', 'inkmember'); ?></option>
                            <option <?php if ($results->payment_period_cycle == 'Y') echo 'selected="selected"'; ?> value="Y"><?php _e('years(s)', 'inkmember'); ?></option>
                        </select>
                    </td>
                </tr>            
    <!--            <tr class="payment trial_select"><td><label for="trial_select">Offer a Subscription Trial:</label></td><td><input id="trial_select" class="trial_yes" <?php if ($results->trial_select == 1) echo 'checked="checkced"'; ?> type="radio" name="trial_select" value="1" />&nbsp;Yes   or&nbsp;&nbsp;&nbsp;<input class="trial_no" id="trial_select" <?php if ($results->trial_select == 0) echo 'checked="checkced"'; ?> type="radio" name="trial_select" value="0"/>&nbsp;No</td></tr>-->
                <tr class="payment trial_price">
                    <td class="label">
                        <label for="trial_price"><?php _e('Second Price:', 'inkmember'); ?></label>
                        <a class="tooltip" title="<?php _e('This is the amount to be charged for second time and further subsequent payments.', 'inkmember'); ?>" href="#"></a>
                    </td>
                    <td>
                        <input type="text" class="small" name="trial_price" id="trial_price" value="<?php echo $results->trial_price; ?>"/>
                    </td>
                </tr>
                <tr class="payment trial_period">
                    <td class="label">
                        <label for="trial_period"><?php _e('Second Period:', 'inkmember'); ?></label>
                        <a class="tooltip" title="<?php _e('Set the time period after which the second price will be charged again.', 'inkmember'); ?>" href="#"></a>
                    </td>
                    <td>
                        <input type="text" class="small" name="trial_period" id="trial_period" value="<?php echo $results->trial_period; ?>"/>&nbsp;
                        <select name="trial_period_cycle" id="trial_period_cycle">
                            <option <?php if ($results->trial_period_cycle == 'D') echo 'selected="selected"'; ?> value="D"><?php _e('day(s)', 'inkmember'); ?></option>
                            <option <?php if ($results->trial_period_cycle == 'W') echo 'selected="selected"'; ?> value="W"><?php _e('week(s)', 'inkmember'); ?></option>
                            <option <?php if ($results->trial_period_cycle == 'M') echo 'selected="selected"'; ?> value="M"><?php _e('month(s)', 'inkmember'); ?></option>
                            <option <?php if ($results->trial_period_cycle == 'Y') echo 'selected="selected"'; ?> value="Y"><?php _e('years(s)', 'inkmember'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr class="payment no_of_payment">
                    <td class="label">
                        <label for="no_of_payment"><?php _e('Number of Payments:', 'inkmember'); ?></label>
                        <a class="tooltip" title="<?php _e('Number of times the Second Recurring Payment will be charged. Enter 0 for unlimited.', 'inkmember'); ?>" href="#"></a>
                    </td>
                    <td>
                        <input class="small" type="text" name="no_of_payment" id="no_of_payment" value="<?php echo $results->no_of_payment; ?>"/>
                    </td>
                </tr>
                <?php if ($results->billing_option == 'one_time') { ?>
                    <tr class="subs_period">
                        <td class="label">
                            <label for="subs_period"><?php _e('Subscription Period:', 'inkmember'); ?></label>
                            <a class="tooltip" title="<?php _e('Time period till the users can access the product.', 'inkmember'); ?>" href="#"></a>
                        </td>
                        <td>
                            <input type="text" class="small" name="subs_period" id="subs_period" value="<?php echo $results->subs_period; ?>"/>&nbsp;
                            <select name="subs_period_cycle" id="subs_period_cycle">
                                <option value=""><?php _e('Select One', 'inkmember'); ?></option>
                                <option <?php if ($results->subs_period_cycle == 'D') echo 'selected="selected"'; ?> value="D"><?php _e('day(s)', 'inkmember'); ?></option>
                                <option <?php if ($results->subs_period_cycle == 'W') echo 'selected="selected"'; ?> value="W"><?php _e('week(s)', 'inkmember'); ?></option>
                                <option <?php if ($results->subs_period_cycle == 'M') echo 'selected="selected"'; ?> value="M"><?php _e('month(s)', 'inkmember'); ?></option>
                                <option <?php if ($results->subs_period_cycle == 'Y') echo 'selected="selected"'; ?> value="Y"><?php _e('years(s)', 'inkmember'); ?></option>
                                <option <?php if ($results->subs_period_cycle == 'U') echo 'selected="selected"'; ?> value="U"><?php _e('Lifetime(22 Years Max)', 'inkmember'); ?></option>
                            </select>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <input type="submit" name="update" value="<?php _e('Update', 'inkmember'); ?>" class="button-primary" />
        <input type="hidden" name="prevent_redunt" value="<?php echo rand(); ?>"/>
    </form>
</div>