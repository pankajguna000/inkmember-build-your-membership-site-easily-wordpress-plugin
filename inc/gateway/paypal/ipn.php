<?php

/**
 *  PHP-PayPal-IPN Example
 *
 *  This shows a basic example of how to use the IpnListener() PHP class to 
 *  implement a PayPal Instant Payment Notification (IPN) listener script.
 *
 */
class InkMember_IPN {

    /**
     * Plugin directory path
     * @var type string
     */
    var $dir_path = '';

    /**
     * Stores site admin email
     * @var type string
     */
    var $admin_email = '';

    /**
     * Stores ipn varified or not
     * @var type boolean
     */
    var $varified = false;

    /**
     * Ipn listener instance
     * @var type object
     */
    var $listener = '';

    /**
     * Stores errors from fraud checks
     * @var type string
     */
    var $errormsg = '';

    /**
     * Stores post back data
     * @var type array
     */
    var $post_data = array();

    /**
     * Stores request back data
     * @var type 
     */
    var $request = array();

    /**
     * Stores payment status
     * @var type string
     */
    var $payment_status = '';

    /**
     * Merchant email
     */
    var $merchant_email = '';

    /**
     * Host Name
     */
    var $host_name = '';

    /**
     * Ipn debug
     */
    var $ipn_debug = true;

    function __construct() {
        
    }

    static function Instance() {
        $obj = new InkMember_IPN();
        $obj->dir_path = plugin_dir_path(__FILE__);
        /**
         * Include Ipnlistener
         */
        include('ipnlistener.php');

        /**
         * Assign merchant id
         */
        $obj->merchant_email = get_option('im_paypal_email');

        /**
         * Set variables
         */
        $obj->admin_email = get_option('admin_email');
        $obj->host_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        /**
         * Instantiate the IpnListener class
         */
        $obj->listener = new IpnListener();

        /**
         * When you are testing your IPN script you should be using a PayPal "Sandbox"
         * account: https://developer.paypal.com  
         * When you are ready to go live change use_sandbox to false. 
         */
        if (get_option('im_sabdbox_mode')) {
            $obj->listener->use_sandbox = true;
        }

        if (!empty($_REQUEST)) {
            $obj->request = $_REQUEST;
        }

        if (!empty($_POST)) {
            $obj->post_data = $_POST;
        }

        if (isset($obj->request['notify']) && $obj->request['notify'] == 'paypalnotify') {
            try {
                $obj->listener->requirePostMethod();
                $obj->varified = $obj->listener->processIpn();
            } catch (Exception $e) {
                error_log($e->getMessage());
                exit(0);
            }


            $obj->payment_status = isset($obj->post_data['payment_status']) ? $obj->post_data['payment_status'] : '';

            add_filter('wp_mail_content_type', array($obj, 'set_html_content_type'));

            //$obj->debug_ipn();
            /**
             * Process the ipn
             */
            $obj->process();

            /**
             *  Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
             */
            remove_filter('wp_mail_content_type', array($obj, 'set_html_content_type'));
        }
    }

    function debug_ipn() {
        /**
         * Debugging ipn
         */
        if ($this->ipn_debug && !empty($this->post_data) & !empty($this->request)) {
            $headers = 'From: ' . $this->host_name . ' <' . $this->admin_email . '>';
            mail($this->admin_email, __('Debug IPN', IM_SLUG), $this->listener->getTextReport(), $headers);
        }
    }

    function process() {
        /*
          The processIpn() method returned true if the IPN was "VERIFIED" and false if it
          was "INVALID".
         */
        if ($this->varified) {

            switch (strtolower($this->payment_status)) :
                case 'completed' :
                    $this->completed();
                    break;
                case 'pending' :
                    $this->pending();
                    break;
                case 'refunded' :
                case 'reversed' :
                case 'chargeback' :
                    $this->refunded();
                    break;
                case 'denied' :
                case 'expired' :
                case 'failed' :
                case 'voided' :
                    $this->failed();
                    break;
            endswitch;
        } else {
            /**
             * manually investigate the invalid IPN
             */
            $headers = 'From: ' . $this->host_name . ' <' . $this->admin_email . '>';
            mail($this->admin_email, __('Invalid IPN', IM_SLUG), $this->listener->getTextReport(), $headers);
        }
    }

    function completed() {

        /**
         * Checking if receiver email and merchant email are similar
         */
        if ($this->post_data['receiver_email'] != $this->merchant_email) {
            //$this->errormsg .= "'receiver_email' does not match: ";
            //$this->errormsg .= $this->post_data['receiver_email'] . "\n";
        }

        if (!empty($this->errormsg)) {

            /**
             * manually investigate errors from the fraud checking
             */
            $body = "IPN failed fraud checks: " . $this->errormsg . "\n\n";
            $body .= $this->listener->getTextReport();
            $headers = 'From: ' . $this->host_name . ' <' . $this->admin_email . '>';
            $subject = __('IPN Fraud Warning', IM_SLUG);
            mail($this->admin_email, $subject, $body, $headers);
        } else {

            /**
             * Entries the transactions on transaction table
             */
            $data = array(
                'post_data' => $this->post_data,
                'req_data' => $this->request
            );
            do_action('im_process_transaction_entry', $data);

            /**
             * Update membership entry
             */
            im_update_transaction_status($data);

            /**
             * Mail to admin for notify his sales
             */
            $this->mail_to_admin();

            /**
             * Mail to user for notify his purchase
             */
            $this->mail_to_user();
        }
    }

    function mail_to_admin() {
        $subject = __($this->host_name . " Payment Received.", IM_SLUG);
        $headers = 'From: ' . $this->host_name . ' <' . $this->admin_email . '>' . "\r\n";
        $message = __('Dear Admin,', IM_SLUG) . "<br/><br/>";
        $message .= sprintf(__('Payment received successfully from %s %s.', IM_SLUG), $this->post_data['first_name'], $this->post_data['last_name']) . "<br/><br/>";
        $message .= __('Transaction Details', IM_SLUG) . "<br/>";
        $message .= "-------------------------------- <br/>";
        $message .= __('Product Name: ', IM_SLUG) . $this->post_data['item_name'] . "<br/>";
        $message .= __('Amount Received: ', IM_SLUG) . $this->post_data['mc_gross'] . "(" . $this->post_data['mc_currency'] . ")<br/>";
        $message .= __('PayPal Email: ', IM_SLUG) . $this->post_data['payer_email'] . "<br/>";
        $message .= __('Transaction ID: ', IM_SLUG) . $this->post_data['txn_id'] . "<br/>";
        $message .= __('Payment type: ', IM_SLUG) . $this->post_data['payment_type'] . "<br/>";
        /**
         * Send email to admin
         */
        wp_mail($this->admin_email, $subject, $message, $headers);
    }

    function mail_to_user() {
        $subject = __($this->host_name . " Thanks for your purchase.", IM_SLUG);
        $headers = 'From: ' . $this->host_name . ' <' . $this->admin_email . '>' . "\r\n" . 'Reply-To: ' . $this->admin_email;
        $content .= __("Dear {$this->post_data['first_name']},", IM_SLUG) . "<br/><br/>";
        $content .= sprintf(__('Your purchase of %s is successful.', IM_SLUG), $this->post_data['item_name']) . "<br/><br/>";
        $content .= __('Transaction Details', IM_SLUG) . "<br/>";
        $content .= "-------------------------------- <br/>";
        $content .= __('Product Name: ', IM_SLUG) . $this->post_data['item_name'] . "<br/>";
        $content .= __('Amount Received: ', IM_SLUG) . $this->post_data['mc_gross'] . "(" . $this->post_data['mc_currency'] . ")<br/>";
        $content .= __('Transaction ID: ', IM_SLUG) . $this->post_data['txn_id'] . "<br/><br/><br/>";
        $content .= __('Warm Regards,', IM_SLUG) . "<br/>";
        $content .= '<a href="' . home_url() . '">' . $this->host_name . "</a><br/>";
        $user_email = get_the_author_meta('user_email', $this->request['uid']);
        /**
         * Send mail to user
         */
        wp_mail($user_email, $subject, $content, $headers); //email to client
    }

    function pending() {
        $subject = __('PayPal IPN - payment pending', IM_SLUG);
        $headers = 'From: ' . __('Admin', IM_SLUG) . ' <' . get_option('admin_email') . '>' . "\r\n";
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $message = __('Dear ' . $this->host_name . ',', IM_SLUG) . "<br/><br/>";
        $message .= sprintf(__('The following payment is pending on your %s website.', IM_SLUG), $this->host_name) . "<br/>";
        $message .= __('Payment Details', IM_SLUG) . "<br/>";
        $message .= '----------------- <br/>';
        $message .= __('Payer PayPal address: ', IM_SLUG) . $this->post_data['payer_email'] . "<br/>";
        $message .= __('Transaction ID: ', IM_SLUG) . $this->post_data['txn_id'] . "<br/>";
        $message .= __('Payer first name: ', IM_SLUG) . $this->post_data['first_name'] . "<br/>";
        $message .= __('Payer last name: ', IM_SLUG) . $this->post_data['last_name'] . "<br/>";
        $message .= __('Payment type: ', IM_SLUG) . $this->post_data['payment_type'] . "<br/>";
        $message .= __('Amount: ', IM_SLUG) . $this->post_data['mc_gross'] . " (" . $this->post_data['mc_currency'] . ")<br/>";

        wp_mail($this->admin_email, $subject, $message, $headers);
    }

    function failed() {
        /**
         * Set expire for his membership
         */
        im_member_expire($this->request['uid'], $this->request['product_key']);

        $subject = __('PayPal IPN - payment failed', IM_SLUG);
        $headers = 'From: ' . $this->host_name . ' <' . get_option('admin_email') . '>' . "<br/>";

        $message = __('Dear ' . $this->host_name . ',', IM_SLUG) . "<br/><br/>";
        $message .= sprintf(__('The following payment has failed on your %s website.', IM_SLUG), $this->host_name) . "<br/><br/>";
        $message .= __('Payment Details', IM_SLUG) . "<br/>";
        $message .= "----------------- <br/>";
        $message .= __('Payer PayPal address: ', IM_SLUG) . $this->post_data['payer_email'] . "<br/>";
        $message .= __('Transaction ID: ', IM_SLUG) . $this->post_data['txn_id'] . "<br/>";
        $message .= __('Payer first name: ', IM_SLUG) . $this->post_data['first_name'] . "<br/>";
        $message .= __('Payer last name: ', IM_SLUG) . $this->post_data['last_name'] . "<br/>";
        $message .= __('Payment type: ', IM_SLUG) . $this->post_data['payment_type'] . "<br/>";
        $message .= __('Amount: ', IM_SLUG) . $this->post_data['mc_gross'] . " (" . $this->post_data['mc_currency'] . ")<br/>";
        wp_mail($this->admin_email, $subject, $message, $headers);
    }

    function refunded() {
        //Set expire membership
        im_member_expire($this->request['uid'], $this->request['product_key']);
        // send an email if payment was refunded
        $subject = __('PayPal IPN - payment refunded/reversed', IM_SLUG);
        $headers = 'From: ' . __($this->host_name, IM_SLUG) . ' <' . $this->admin_email . '>' . "\r\n";
        $message = __('Dear Admin,', IM_SLUG) . "<br/>";
        $message .= sprintf(__('The following payment has been marked as refunded on your %s website.', IM_SLUG), $this->host_name) . "<br/><br/>";
        $message .= __('Payment Details', IM_SLUG) . "<br/>";
        $message .= "----------------- <br/>";
        $message .= __('Payer PayPal address: ', IM_SLUG) . $this->post_data['payer_email'] . "<br/>";
        $message .= __('Transaction ID: ', IM_SLUG) . $this->post_data['txn_id'] . "<br/>";
        $message .= __('Payer first name: ', IM_SLUG) . $this->post_data['first_name'] . "<br/>";
        $message .= __('Payer last name: ', IM_SLUG) . $this->post_data['last_name'] . "<br/>";
        $message .= __('Payment type: ', IM_SLUG) . $this->post_data['payment_type'] . "<br/>";
        $message .= __('Reason code: ', IM_SLUG) . $this->post_data['reason_code'] . "<br/>";
        $message .= __('Amount: ', IM_SLUG) . $this->post_data['mc_gross'] . " (" . $this->post_data['mc_currency'] . ")<br/>";

        wp_mail($this->admin_email, $subject, $message, $headers);
    }

    function set_html_content_type() {
        return 'text/html';
    }

}

add_action('plugins_loaded', array('InkMember_IPN', 'Instance'));

function debug() {
    $file = 'jsutcheck.php';
// Open the file to get existing content
// Append a new person to the file
    $post = '<?php echo "Post Values";' . print_r($_REQUEST, true) . ' ?>';
// Write the contents back to the file
    file_put_contents($file, $post);
    ini_set('log_errors', true);
    ini_set('error_log', dirname(__FILE__) . '/ipn_errors.log');
}

//debug();
