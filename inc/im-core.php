<?php

function im_remove_login($item) {
    if (is_user_logged_in()) {
        $item['exclude'] = get_option('im_login');
    }
    return $item;
}

add_filter('wp_list_pages_excludes', 'im_remove_login');

function im_the_content_filter($content) {
    global $post;
    $user_id = get_current_user_id();
    $user_key = im_get_user_member_key($user_id);
    $post_key = im_get_post_lavel($post->ID);
    //if (is_user_logged_in() && $post->post_type != 'page') {
    if ($post_key && $user_key)
        $result = array_intersect($user_key, $post_key);
    if (!empty($result)) {
        return $content;
    } elseif (empty($post_key)) {
        $extrn_content = apply_filters('im_pt_content', $content);
        return $extrn_content;
    } else {
        return im_content_lock($post_key);
    }
}

add_filter('the_content', 'im_the_content_filter');

function im_content_lock($post_key) {
    //if (is_user_logged_in()) {
    $user_id = get_current_user_id();
    $payment_mode = get_option('im_payment_mde');
    global $wpdb, $im_tbl_product;
    if ($post_key) {
        $count = 1;
        $either_text = '';
        $output = '';
        $str = '';
        $auth_forum = '';
        $footer_script = '';
        foreach ($post_key as $val) {
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
                    $subscription_price = "<b>" . __('Pricing:', 'inkmember') . " {$ke['product_price']} {$ke['currency']}</b><br/><br/>";
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
                if (isset($_REQUEST['redirect_paypal']) && $_REQUEST['redirect_paypal'] == 'true') {
                    $req_form = $_REQUEST['form_id'];
                    $output .= '<script>/* <![CDATA[ */';
                    $output .= 'jQuery(document).ready(function(){';
                    $output .= 'jQuery("#payment_form_' . $count . ' #frm_payment_' . $req_form . '").submit();';
                    $output .= '});';
                    $output .= '/* ]]> */</script>';
                } else {
                    $output .= '<script type="text/javascript">/* <![CDATA[ */'
                            . 'jQuery(document).ready(function(){'
                            . 'jQuery("#im_pay_submit_' . $count . '").click(function(e){'
                            . 'e.preventDefault();'
                            . 'jQuery("#payment_form_' . $count . ' #frm_payment_' . $count . '").submit();});'
                            . '});'
                            . '/* ]]> */</script>';
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
        if (is_single() || is_page() || is_front_page()) {
            return $content;
        }
    }
}

/**
 * Add setting menu on admin panel 
 */
function im_admin_menu() {
    add_menu_page(__('InkMember', IM_SLUG), __('InkMember', IM_SLUG), 'manage_options', 'inkmember', 'im_setting', plugins_url('/images/icon.png', __DIR__));
    add_submenu_page('inkmember', __('Settings', IM_SLUG), __('Settings', IM_SLUG), 'manage_options', 'setting', 'im_setting_page');
    add_submenu_page('inkmember', __('Transaction', IM_SLUG), __('Transaction', IM_SLUG), 'manage_options', 'transation', 'im_transaction');
    add_submenu_page('inkmember', __('Help', IM_SLUG), __('Help', IM_SLUG), 'manage_options', 'help', 'im_help');
}

add_action('admin_menu', 'im_admin_menu');

function im_setting_page() {
    if (isset($_POST['submit'])) {
        if (isset($_POST['payment_mode'])) {
            update_option('im_payment_mde', $_POST['payment_mode']);
        }
        if (isset($_POST['pay_cash_url'])) {
            update_option('im_pay_cash_url', $_POST['pay_cash_url']);
        }
        if (isset($_POST['order_sign_page'])) {
            update_option('im_order_sign_page', wp_kses($_POST['order_sign_page'], array()));
        }
        if (isset($_POST['default_pricing_page'])) {
            update_option('im_pricing_page', wp_kses($_POST['default_pricing_page'], array()));
        }
        if (isset($_POST['return_member_page'])) {
            update_option('im_return_page', wp_kses($_POST['return_member_page'], array()));
        }
        if (isset($_POST['paypal_email'])) {
            update_option('im_paypal_email', wp_kses($_POST['paypal_email'], array()));
        }
        if (isset($_POST['sandbox_mode'])) {
            update_option('im_sabdbox_mode', wp_kses($_POST['sandbox_mode'], array()));
        }
        if (isset($_POST['paypal_locale'])) {
            update_option('im_paypal_locale', wp_kses($_POST['paypal_locale'], array()));
        }
        if (isset($_POST['mailling_list_type'])) {
            update_option('im_mailling_list_type', wp_kses($_POST['mailling_list_type'], array()));
        }
        if (isset($_POST['subs_email'])) {
            update_option('im_subs_email', wp_kses($_POST['subs_email'], array()));
        }
        if (isset($_POST['debug_ipn'])) {
            update_option('im_debut_ipn', wp_kses($_POST['debug_ipn'], array()));
        }
        if (isset($_POST['im_recaptcha_setting'])) {
            update_option('im_recaptcha', wp_kses($_POST['im_recaptcha_setting'], array()));
        }
        if (isset($_POST['im_recaptcha_public_setting'])) {
            update_option('im_recaptcha_public', wp_kses($_POST['im_recaptcha_public_setting'], array()));
        }
        if (isset($_POST['im_recaptcha_private_setting'])) {
            update_option('im_recaptcha_private', wp_kses($_POST['im_recaptcha_private_setting'], array()));
        }
    }
    require_once dirname(__FILE__) . '/view/setting-page.php';
}

/**
 * Function: im_currency
 * @return type array
 */
function im_currency() {
    $currency = array(
        'USD' => __('U.S. Dollar', IM_SLUG),
        'AUD' => __('Australian Dollar', IM_SLUG),
        'BRL' => __('Brazilian Real', IM_SLUG),
        'CAD' => __('Canadian Dollar', IM_SLUG),
        'CZK' => __('Czech Koruna', IM_SLUG),
        'DKK' => __('Danish Krone', IM_SLUG),
        'EUR' => __('Euro', IM_SLUG),
        'HKD' => __('Hong Kong Dollar', IM_SLUG),
        'HUF' => __('Hungarian Forint', IM_SLUG),
        'ILS' => __('Israeli New Sheqel', IM_SLUG),
        'JPY' => __('Japanese Yen', IM_SLUG),
        'MYR' => __('Malaysian Ringgit', IM_SLUG),
        'MXN' => __('Mexican Peso', IM_SLUG),
        'NOK' => __('Norwegian Krone', IM_SLUG),
        'NZD' => __('New Zealand Dollar', IM_SLUG),
        'PHP' => __('Philippine Peso', IM_SLUG),
        'PLN' => __('Polish Zloty', IM_SLUG),
        'GBP' => __('Pound Sterling', IM_SLUG),
        'SGD' => __('Singapore Dollar', IM_SLUG),
        'SEK' => __('Swedish Krona', IM_SLUG),
        'CHF' => __('Swiss Franc', IM_SLUG),
        'TWD' => __('Taiwan New Dollar', IM_SLUG),
        'THB' => __('Thai Baht', IM_SLUG),
        'TRY' => __('Turkish Lira', IM_SLUG),
        'RUB' => __('Russian Ruble', IM_SLUG),
    );
    return $currency;
}

function im_setting() {
    ?>
    <div id="inkmember_wrap">
        <div class="member_head">
            <div id="icon-options-general" class="icon32"></div>
            <h2><?php _e('InkMember', 'inkmember'); ?></h2>
        </div>
        <?php
        //Delete Product
        if ($_REQUEST['page'] == 'inkmember' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') {
            $pid = $_REQUEST['pid'];
            global $wpdb, $im_tbl_product;
            $wpdb->query($wpdb->prepare("DELETE FROM $im_tbl_product WHERE PID = %d", $pid));
        }
        //Edit Product
        if ($_REQUEST['page'] == 'inkmember' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
            print '<a title="Back to membership product list" class="member_navi" href="' . admin_url('/admin.php?page=inkmember&action=reset') . '"><img src="' . plugins_url('images/back.png', __DIR__) . '"/></a>';
            $pid = $_REQUEST['pid'];
            im_edit_product($pid);
            global $wpdb, $im_tbl_product;
            $results = $wpdb->get_row($wpdb->prepare("SELECT * FROM $im_tbl_product WHERE PID = %d", $pid));
            include_once dirname(__FILE__) . '/view/edit-product.php';
        }
        if ($_REQUEST['page'] == 'inkmember' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_new') {
            //Add product
            im_add_product();
            print '<a title="Back to membership product list" class="member_navi" href="' . admin_url('/admin.php?page=inkmember&action=reset') . '"><img src="' . plugins_url('images/back.png', __DIR__) . '"/></a>';
            include_once dirname(__FILE__) . '/view/add-product.php';
        }
        //List Product
        if ($_REQUEST['page'] == 'inkmember' && !isset($_REQUEST['action']) || $_REQUEST['action'] == 'reset' || $_REQUEST['action'] == 'delete') {
            print '<a title="Add membership product" class="member_navi" href="' . admin_url('/admin.php?page=inkmember&action=add_new') . '"><img src="' . plugins_url('images/add.png', __DIR__) . '"/></a>';
            require_once dirname(__FILE__) . '/view/product-list.php';
        }
        ?>
    </div>
    <?php
}

function im_add_product() {
    if (isset($_POST['add'])) {
        $merchant_id = get_option('im_paypal_email');
        if (get_option('im_payment_mde') == 'cashlater' || $merchant_id != '') {
            $prevent_redunt = $_POST['prevent_redunt'];
            if (get_option('im_prevent_redunt') != $prevent_redunt) {
                $my_product = array();
                $my_product['product_name'] = wp_kses($_POST['product_name'], array());
                $my_product['billing_option'] = wp_kses($_POST['billing_option'], array());
                $my_product['p_button_img'] = wp_kses($_POST['payment_button'], array());
                $my_product['currency'] = wp_kses($_POST['currency'], array());
                $my_product['product_price'] = wp_kses($_POST['product_price'], array());
                $my_product['payment_period'] = wp_kses($_POST['payment_period'], array());
                $my_product['payment_period_cycle'] = wp_kses($_POST['payment_period_cycle'], array());
                $my_product['no_of_payment'] = wp_kses($_POST['no_of_payment'], array());
                $my_product['trial_select'] = wp_kses($_POST['trial_select'], array());
                $my_product['trial_price'] = wp_kses($_POST['trial_price'], array());
                $my_product['trial_period'] = wp_kses($_POST['trial_period'], array());
                $my_product['trial_period_cycle'] = wp_kses($_POST['trial_period_cycle'], array());
                $my_product['subs_period'] = wp_kses($_POST['subs_period'], array());
                $my_product['subs_period_cycle'] = wp_kses($_POST['subs_period_cycle'], array());
                $my_product['member_key'] = wp_kses($_POST['member_key'], array());
                global $wpdb, $im_tbl_product;
                $wpdb->insert($im_tbl_product, $my_product);
                if (get_option('im_prevent_redunt') == '') {
                    add_option('im_prevent_redunt', $prevent_redunt);
                } else {
                    update_option('im_prevent_redunt', $prevent_redunt);
                }
            }
        } else {
            echo '<script>alert("###Error###\n---------------------------------------------\n'
            . __('Set the merchant email for paypal before your add any product!', IM_SLUG) . '");</script>';
        }
    }
}

function im_edit_product($pid) {
    if (isset($_POST['update'])) {
        $my_product = array();
        $my_product['product_name'] = wp_kses($_POST['product_name'], array());
        $my_product['billing_option'] = wp_kses($_POST['billing_option'], array());
        $my_product['p_button_img'] = wp_kses($_POST['payment_button'], array());
        $my_product['currency'] = wp_kses($_POST['currency'], array());
        $my_product['product_price'] = wp_kses($_POST['product_price'], array());
        $my_product['payment_period'] = wp_kses($_POST['payment_period'], array());
        $my_product['payment_period_cycle'] = wp_kses($_POST['payment_period_cycle'], array());
        $my_product['no_of_payment'] = wp_kses($_POST['no_of_payment'], array());
        $my_product['trial_select'] = wp_kses($_POST['trial_select'], array());
        $my_product['trial_price'] = wp_kses($_POST['trial_price'], array());
        $my_product['trial_period'] = wp_kses($_POST['trial_period'], array());
        $my_product['trial_period_cycle'] = wp_kses($_POST['trial_period_cycle'], array());
        $my_product['subs_period'] = wp_kses($_POST['subs_period'], array());
        $my_product['subs_period_cycle'] = wp_kses($_POST['subs_period_cycle'], array());
        global $wpdb, $im_tbl_product;
        $wpdb->update($im_tbl_product, $my_product, array('PID' => $pid));
    }
}

function im_notify_head() {
    require_once dirname(__FILE__) . '/view/login-header.php';
}

function im_notify_footer() {
    require_once dirname(__FILE__) . '/view/login-content.php';
}

function im_transaction() {
    if (isset($_REQUEST['id']) && $_REQUEST['page'] = 'transation') {
        $id = $_REQUEST['id'];
        im_delete_trans($id);
    }
    require_once dirname(__FILE__) . '/view/transaction.php';
}

function im_help() {
    require_once dirname(__FILE__) . '/view/help.php';
}
