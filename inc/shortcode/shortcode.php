<?php

//Shortcode for private content. Private content may protected by also multiple ids
add_shortcode('private_content', 'im_shortcode_button');

function im_shortcode_button($atts, $content = null) {
    //extracts our attrs . if not set set default
    extract(shortcode_atts(array('id' => '1'), $atts));
    $payment_mode = get_option('im_payment_mde');
    $user_id = get_current_user_id();
    $user_key = im_get_user_member_key($user_id);
    $private_keys = explode(',', $id);
    global $wpdb, $im_tbl_product;
    $keys = array();
    foreach ($private_keys as $key) {
        $keys[] = im_get_private_key($key);
    }

    if ($keys && $user_key)
        $result = array_intersect($user_key, $keys);
    if (!empty($result)) {
        return $content;
    } else {
        $count = 1;
        $either_text = '';
        $output = '';
        $str = '';
        $auth_forum = '';
        foreach ($keys as $val) {
            $querys[] = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $im_tbl_product . ' WHERE member_key = %s', $val), ARRAY_A);
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
                    $subscription_price = sprintf(__('<b>Pricing: %1$s %2$s</b><br/><br/>', IM_SLUG), $ke['product_price'], $ke['currency']);
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
                        $installment = sprintf(__(", for %s Installments"), $ke['no_of_payment']);
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
                $order['submit_btn'] = true;
                $order['form_id'] = $count;
                $form = '<div id="payment_form_' . $count . '">';
                if ($payment_mode == 'paypal') {
                    $form .= im_paypal_getway_process($order);
                }
                $form .= '</div>';
            }
            $either_text = ($count > 1) ? ' either ' : '';
            $output .= $str;
            $output .= $form;
            if (is_user_logged_in()) {
                if (isset($_GET['redirect_paypal']) && $_GET['redirect_paypal'] == 'true') {
                    $req_form = $_GET['form_id'];
                    $output .= '<script>'
                            . 'jQuery(document).ready(function(){'
                            . 'jQuery("#payment_form_' . $count . ' #frm_payment_' . $req_form . '").submit();'
                            . '});'
                            . '</script>';
                } else {
                    $output .= '<script type="text/javascript">'
                            . 'jQuery(document).ready(function(){'
                            . 'jQuery("#im_pay_submit_' . $count . '").click(function(e){'
                            . 'e.preventDefault();'
                            . 'jQuery("#payment_form_' . $count . ' #frm_payment_' . $count . '").submit();});'
                            . '});'
                            . '</script>';
                }
            }
            $submit_id = 'im_pay_submit_' . $count;
            $requested_link = '#';
            $button_class = 'buy_btn';
            if (!is_user_logged_in()) {
                $auth_forum = im_autho_form('none', 'im_log_form');
                $submit_id = 'im_log_pop_' . $count;
                $requested_link = get_permalink() . '?redirect_paypal=true&amp;form_id=' . $count;
            }
            if ($payment_mode == 'cashlater') {
                $requested_link = get_option('im_pay_cash_url');
                $submit_id = 'nothing';
                $button_class = 'nothing';
            }
            $output .= '<p><a id="' . $submit_id . '" data-requrl="' . $requested_link . '" class="' . $button_class . ' purchase_btn" href="' . $requested_link . '">' . __('Buy Now', IM_SLUG) . '</a></p>';
            $count++;
        }
        $content = '<div class="protected-notice">';
        $content.= '<h2>' . sprintf(__('Restricted Access. You must be a member to access this content. Get %s package below to access the Content.', IM_SLUG), $either_text) . '</h2>';
        $content.= '</div>';
        $content.= '<div id="im_pricing">';
        $content.= $output . $auth_forum;
        $content.= '</div>';
    }
    return $content;
}

//Login / register form shortcode
add_shortcode('login_reg', 'im_autho_form');

//Shortcode for pricing list, It may listing multiple pricing list 
add_shortcode('im_pricing', 'im_pricing_shortcode');

function im_pricing_shortcode($atts, $content = null) {
    //extracts our attrs . if not set set default
    extract(shortcode_atts(array('id' => '1'), $atts));
    $ids = explode(',', $id);
    $keys = array();
    if (!empty($ids)) {
        foreach ($ids as $id) {
            $keys[] = im_get_products($id);
        }
    }
    if (is_user_logged_in()) {
        if ($keys) {
            $user_id = get_current_user_id();
            $order = array();
            foreach ($keys as $key) {
                if ($key) {
                    foreach ($key as $ke) {
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
                            $subscription_price = sprintf(__('Your subscription price is: %s <br/>', IM_SLUG), $ke['product_price']);
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
                                $installment = sprintf(__(", for %s Installments"), $ke['no_of_payment']);
                            } else {
                                $installment = '';
                            }
                            $second_billing_terms = sprintf(__(', Then&nbsp;%1$s&nbsp;%2$s for each %3$s&nbsp;%4$s %5$s', IM_SLUG), $ke['trial_price'], $ke['currency'], $ke['trial_period'], $second_period, $installment);
                        }

                        $str = sprintf(__('Your subscription Name is: %s <br/>', IM_SLUG), $ke['product_name']);
                        $str .= $subscription_price;
                        if ($ke['billing_option'] == 'recurring') {
                            $str .= $billing_terms . $second_billing_terms;
                        }

                        $form = im_paypal_getway_process($order);
                        return '<div id="im_pricing">' . $str . $form . '</div>';
                    }
                }
            }
        }
    } else {
        $login_url = IM_LOGING_PAGE;
        return sprintf('<a class="nofity" href="%s">' . __("Please login or signup to see pricing", IM_SLUG) . '</a>', $login_url);
    }
}
