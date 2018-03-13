<?php

/**
 * Purchase action
 */
function im_purchase_action() {
    if (isset($_REQUEST['imaction']) && $_REQUEST['imaction'] == 'membership') {
        if (isset($_REQUEST['purchase_key']) && $_REQUEST['purchase_key'] != '') {
            global $wpdb, $im_tbl_product;
            $member_key = $_REQUEST['purchase_key'];
            $user_id = get_current_user_id();
            $querys[] = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $im_tbl_product . ' WHERE member_key = %s', $member_key), ARRAY_A);
            foreach ($querys as $ke) {
                $order['item_name'] = $ke['product_name'];
                $order['currency_code'] = $ke['currency'];
                $order['billing_type'] = $ke['billing_option'];
                $order['p_button'] = $ke['p_button_img'];
                $order['amount'] = $ke['product_price'];
                $order['installment'] = $ke['no_of_payment'];
                $order['f_period'] = $ke['payment_period'];
                $order['f_cycle'] = $ke['payment_period_cycle'];
                $order['trial_select'] = $ke['trial_select'];
                $order['s_price'] = $ke['trial_price'];
                $order['s_period'] = $ke['trial_period'];
                $order['s_cycle'] = $ke['trial_period_cycle'];
                $order['subs_period'] = $ke['subs_period'];
                $order['subs_period_cycle'] = $ke['subs_period_cycle'];
                $order['j_button_img'] = $ke['j_button_img'];
                $order['member_key'] = $ke['member_key'];
                $order['user_id'] = $user_id;
                $order['product_key'] = IM_MEMBER_ID . $ke['PID'];
                if ($ke['billing_option'] == 'one_time') {
                    $subscription_price = "<b>Pricing: {$ke['product_price']} {$ke['currency']}</b><br/><br/>";
                } else {
                    $subscription_price = '';
                }
                if ($ke['billing_option'] == 'recurring') {
                    $period = im_payment_period($ke['payment_period'], $ke['payment_period_cycle']);
                    $second_period = im_payment_period($ke['trial_period'], $ke['trial_period_cycle']);
                    if ($ke['billing_option'] == 'recurring') {
                        $billing_terms = sprintf(__('Billing Terms: %1$s&nbsp;%2$s for %3$s&nbsp;%4$s', IM_SLUG), $ke['product_price'], $ke['currency'], $ke['payment_period'], $period);
                    } else {
                        $billing_terms = '';
                    }
                    if ($ke['no_of_payment'] > 0) {
                        $installment = sprintf(__(", for %s Installments", IM_SLUG), $ke['no_of_payment']);
                    } else {
                        $installment = '';
                    }
                    $second_billing_terms = sprintf(__(', Then&nbsp;%1$s&nbsp;%2$s for each %3$s&nbsp;%4$s %5$s', IM_SLUG), $ke['trial_price'], $ke['currency'], $ke['trial_period'], $second_period, $installment);
                }

                $str = "<b>{$ke['product_name']}</b><br/>";
                $str .= $subscription_price;
                if ($ke['billing_option'] == 'recurring') {
                    $str .= $billing_terms . $second_billing_terms;
                }
                $order['submit_btn'] = false;
                $form = im_paypal_getway_process($order);
            }
            echo '<div id="im_pricing">';
            if (is_user_logged_in()) {
                echo $form;
                echo '<script>setTimeout("document.frm_payment_method.submit()",01);</script>';
            }
            echo '</div>';

            if (!is_user_logged_in()) {
                add_action('wp_footer', 'im_purchase_footer_script');
            }
        } else {
            echo '<script type="text/javascript">alert("Wrong access");</script>';
            wp_redirect(site_url());
        }
    }
}

add_action('init', 'im_purchase_action');

function im_purchase_footer_script() {
    if (isset($_REQUEST['purchase_key']) && $_REQUEST['purchase_key'] == '')
        wp_die(__('Product not found', IM_SLUG));
    $member_key = $_REQUEST['purchase_key'];
    echo im_autho_form('none', 'im_log_form', true);
    echo '<script type="text/javascript">jQuery(document).ready(function(){ jQuery(".buy_btn").click(); jQuery(".redirect_to").val(jQuery(".buy_btn").attr("href"));});</script>';
    print '<p><a id="im_log_pop" class="buy_btn" href="' . site_url('/?imaction=membership&amp;purchase_key=' . $member_key) . '"></a></p>';
}
