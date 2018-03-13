<?php

function im_process_paypal_transaction($data) {
    global $wpdb, $im_transaction;

    // since paypal sends over the date as a string, we need to convert it
    // into a mysql date format. There will be a time difference due to PayPal's
    // US pacific time zone and your server time zone
    $payment_date = strtotime($data['post_data']['payment_date']);
    $payment_date = strftime('%Y-%m-%d %H:%M:%S', $payment_date);


    //setup some values that are not always sent
    if (isset($data['req_data']['uid']))
        $uid = $data['req_data']['uid'];
    else
        $uid = '';

    if (isset($data['post_data']['reason_code']))
        $reason_code = $data['post_data']['reason_code'];
    else
        $reason_code = '';

    // check and make sure this transaction hasn't already been added
    $results = $wpdb->get_var($wpdb->prepare("SELECT txn_id FROM $im_transaction WHERE txn_id = %s LIMIT 1", inkthemes_clean($data['post_data']['txn_id'])));

    if (!$results) :

        // @todo Change to Insert
        $order_val = array();
        $order_val['uid'] = inkthemes_clean($uid);
        $order_val['first_name'] = inkthemes_clean($data['post_data']['first_name']);
        $order_val['last_name'] = inkthemes_clean($data['post_data']['last_name']);
        $order_val['payer_email'] = inkthemes_clean($data['post_data']['payer_email']);
        $order_val['residence_country'] = inkthemes_clean($data['post_data']['residence_country']);
        $order_val['transaction_subject'] = inkthemes_clean($data['post_data']['transaction_subject']);
        $order_val['item_name'] = inkthemes_clean($data['post_data']['item_name']);
        $order_val['item_number'] = inkthemes_clean($data['post_data']['item_number']);
        $order_val['payment_type'] = inkthemes_clean($data['post_data']['payment_type']);
        $order_val['payer_status'] = inkthemes_clean($data['post_data']['payer_status']);
        $order_val['payer_id'] = inkthemes_clean($data['post_data']['payer_id']);
        $order_val['receiver_id'] = inkthemes_clean($data['post_data']['receiver_id']);
        $order_val['parent_txn_id'] = inkthemes_clean($data['post_data']['parent_txn_id']);
        $order_val['txn_id'] = ($data['post_data']['txn_id']);
        $order_val['mc_gross'] = inkthemes_clean($data['post_data']['mc_gross']);
        $order_val['mc_fee'] = inkthemes_clean($data['post_data']['mc_fee']);
        $order_val['payment_status'] = inkthemes_clean($data['post_data']['payment_status']);
        $order_val['pending_reason'] = inkthemes_clean($data['post_data']['pending_reason']);
        $order_val['txn_type'] = inkthemes_clean($data['post_data']['txn_type']);
        $order_val['tax'] = inkthemes_clean($data['post_data']['tax']);
        $order_val['mc_currency'] = inkthemes_clean($data['post_data']['mc_currency']);
        $order_val['reason_code'] = inkthemes_clean($reason_code);
        $order_val['custom'] = inkthemes_clean($data['post_data']['custom']);
        $order_val['test_ipn'] = inkthemes_clean($data['post_data']['test_ipn']);
        $order_val['payment_date'] = $payment_date;
        $order_val['create_date'] = current_time('mysql');
        $wpdb->insert($im_transaction, $order_val);
    // ad transaction already exists so it must be an update via PayPal IPN (refund, etc)
    // @todo send through prepare
    else:

        $update = "UPDATE " . $im_transaction .
                " payment_status = '" . $wpdb->escape(inkthemes_clean($data['post_data']['payment_status'])) . "'," .
                " mc_gross = '" . $wpdb->escape(inkthemes_clean($data['post_data']['mc_gross'])) . "'," .
                " txn_type = '" . $wpdb->escape(inkthemes_clean($data['post_data']['txn_type'])) . "'," .
                " reason_code = '" . $wpdb->escape(inkthemes_clean($reason_code)) . "'," .
                " mc_currency = '" . $wpdb->escape(inkthemes_clean($data['post_data']['mc_currency'])) . "'," .
                " test_ipn = '" . $wpdb->escape(inkthemes_clean($data['post_data']['test_ipn'])) . "'," .
                " create_date = '" . $wpdb->escape($payment_date) . "'" .
                " WHERE txn_id ='" . $wpdb->escape($data['post_data']['txn_id']) . "'";

        //Updating transaction that was already found
        $results = $wpdb->query($update);

    endif;
    $member_key = $data['req_data']['member_key'];
    $member_id = $data['req_data']['product_key'];
    //An updation for make valid member
    im_update_member($uid, $member_id, $member_key);
    //Set membership expiry
    im_set_member_expiry($uid, $member_key, $member_id);
}

add_action('im_process_transaction_entry', 'im_process_paypal_transaction');

function im_update_transaction_status($data) {
    $args = array(
        'member_' . $data['req_data']['member_key'] => array(
            'user_id' => $data['req_data']['uid'],
            'member_key' => $data['req_data']['member_key'],
            'product_key' => $data['req_data']['product_key'],
            'payment_status' => isset($data['post_data']['payment_status']) ? $data['post_data']['payment_status'] : $data['post_data']['st'],
        )
    );
    update_option('im_payment_status', $args);
}

function im_get_transaction_status($values) {
    $status = get_option('im_payment_status');
    $data = $status['member_' . $values['member_key']]['payment_status'];
    return $data;
}

function im_transaction_status() {
    $status = im_get_transaction_status($_REQUEST);
    //Checking payment success or not
    if ($status == 'Completed' || $status == true || $status == 'subscr_signup') {
        $status = __('<h4>Thank you for your payment</h4>', IM_SLUG);
        $status .= __("<p>Payment Received<p>", IM_SLUG);
        $status .= __("<p>Your subcription has been successfully created.</p>", IM_SLUG);
    } else {
        $status = __("<p>Sorry, Your payment has been failed</p>", IM_SLUG);
    }
    return $status;
}

add_shortcode('payment_status', 'im_transaction_status');
