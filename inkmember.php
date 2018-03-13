<?php

/**
 * Plugin Name: Ink - Membership WordPress Plugin 
 * Plugin URI: https://www.inkthemes.com/plugin/membership-plugin-for-wordpress/
 * Description: Ink Member plugin provides you the option to protect your content and sell them to the audience once you get paid.
 * Author: InkThemes
 * Text Domain: inkmember
 * Author URI: https://www.inkthemes.com
 * Version: 1.0.3
 */
load_plugin_textdomain('inkmember', false, basename(dirname(__FILE__)) . '/languages/');
define('IM_VERSION', '1.4.3');
define('IM_SLUG', 'inkmember');
define('IM_MEMBER_ID', 'membership');
define('IM_PRICING_PAGE', get_option('im_pricing_page'));
define('IM_LOGING_PAGE', get_option('im_order_sign_page'));
define('IM_PLUGIN_PATH', WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)));

class INK_Member {

    static public $instance = '';

    /**
     * Inkmember initialization
     */
    static function init() {
        self::$instance = new INK_Member();
        self::$instance->inc();
        add_action('admin_print_styles', array(self::$instance, '_css'));
        add_action('wp_enqueue_scripts', array(self::$instance, '_front_css'));

        add_action('admin_print_scripts', array(self::$instance, '_adminJs'));
        add_action('wp_enqueue_scripts', array(self::$instance, '_frontJs'));
    }

    static function Install() {
        if (get_option('im_payment_mde') == '') {
            update_option('im_payment_mde', 'paypal');
        }

        $userid = get_current_user_id();
//Creating all ads page
        $pages = get_option('im_login');
        if (empty($pages)) {
            $my_page = array(
                'ID' => false,
                'post_type' => 'page',
                'post_name' => 'login',
                'ping_status' => 'closed',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'post_author' => $userid,
                'post_content' => '[login_reg]',
                'post_title' => __('Login', THEME_SLUG),
                'post_excerpt' => ''
            );
            $pages_id = wp_insert_post($my_page);
            if ($pages_id) {
                update_option('im_login', $pages_id);
            }
        }
        //set login page url 
        $login_page = get_option('im_login');
        $login_url = site_url('?page_id=' . $login_page);
        update_option('im_order_sign_page', $login_url);
        //Creating payment status page
        $payment_pages = get_option('payment_status');
        if (empty($payment_pages)) {
            $my_page = array(
                'ID' => false,
                'post_type' => 'page',
                'post_name' => 'payment-status',
                'ping_status' => 'closed',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'post_author' => $userid,
                'post_content' => '',
                'post_title' => __('Payment Status', THEME_SLUG),
                'post_excerpt' => '[payment_status]'
            );
            $pages_id = wp_insert_post($my_page);
            if ($pages_id) {
                update_option('payment_status', $pages_id);
            }
        }
    }

    /**
     * Include libraries
     */
    function inc() {
        include_once dirname(__FILE__) . '/inc/im-db.php';
        include_once dirname(__FILE__) . '/inc/im-process.php';
        include_once dirname(__FILE__) . '/inc/im-core.php';
        include_once dirname(__FILE__) . '/inc/user-auth.php';
        include_once dirname(__FILE__) . '/inc/metabox.php';
        include_once dirname(__FILE__) . '/inc/shortcode/tinyMCE.php';
        include_once dirname(__FILE__) . '/inc/shortcode/shortcode.php';
        include_once dirname(__FILE__) . '/inc/gateway/paypal/paypal.php';
        include_once dirname(__FILE__) . '/inc/gateway/paypal/process.php';
        include_once dirname(__FILE__) . '/inc/purchase_action.php';
        include_once dirname(__FILE__) . '/inc/gateway/paypal/ipn.php';
    }

    /**
     * Load css for admin page
     */
    function _css() {
        wp_register_style('im-admin-style', plugins_url('/css/im-admin-style.css', __FILE__));
        wp_enqueue_style('im-admin-style');
    }

    /**
     * Load css for front end style
     */
    function _front_css() {
        wp_enqueue_style('im-front-style', plugins_url('/css/front-style.css', __FILE__), '', '', 'all');
    }

    /**
     * Load js for admin page
     */
    function _adminJs() {

// Localize the script with new data
        $translation_array = array(
            'first_price' => __('First Price:', 'inkmember')
        );
        wp_localize_script('some_handle', 'object_name', $translation_array);

// Enqueued script with localized data.
        wp_enqueue_script('some_handle');
        wp_register_script('im-admin-script', plugins_url('/js/im-admin.js', __FILE__), array('jquery'), '', true);
        wp_register_script('im-edit-script', plugins_url('/js/im-edit.js', __FILE__), array('jquery'), '', true);
        wp_enqueue_script('im-tooltip', plugins_url('/js/jquery.tipsy.js', __FILE__), array('jquery'), '', true);
        wp_enqueue_script('im-admin-script');
        wp_enqueue_script('im-edit-script');
    }

    /**
     * Load js for front end 
     */
    function _frontJs() {
        if (!is_admin()) {
            wp_enqueue_script('im-litebox', plugins_url('/js/jquery.lightbox_me.js', __FILE__), array('jquery'));
            wp_enqueue_script('im-scripts', plugins_url('/js/script.js', __FILE__), array('jquery'));
        }
    }

}

INK_Member::init();

register_activation_hook(__FILE__, array('INK_Member', 'Install'));
