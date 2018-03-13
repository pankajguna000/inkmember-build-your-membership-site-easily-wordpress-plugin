<?php

function im_update_member($user_id, $member_id, $member_key) {
    update_user_meta($user_id, $member_id, $member_key);
    /**
     * call img_set_member_label function to 
     * Set the member_access_label
     */
    img_set_member_label($user_id);
}

function im_get_member_key() {
    global $wpdb, $im_tbl_product;
    $results = $wpdb->get_col("SELECT member_key FROM $im_tbl_product");
    return $results;
}

function im_get_private_key($pid) {
    global $wpdb, $im_tbl_product;
    $sql = $wpdb->prepare("SELECT member_key FROM $im_tbl_product WHERE PID = %d", $pid);
    $private_key = $wpdb->get_col($sql);
    if ($private_key) {
        return $private_key[0];
    }
}

function im_get_products($pid) {
    global $wpdb, $im_tbl_product;
    $sql = $wpdb->prepare("SELECT * FROM $im_tbl_product WHERE PID = %d", $pid);
    $private_key = $wpdb->get_results($sql, ARRAY_A);
    if ($private_key) {
        return $private_key;
    }
}

function im_get_post_lavel($post_id = null) {
    $membership = im_get_poducts();
    $member_lavel = array();
    if ($membership) {
        foreach ($membership as $member) {
            $member_id = IM_MEMBER_ID . $member->PID;
            if (get_post_meta($post_id, $member_id, true))
                $member_lavel[] = get_post_meta($post_id, $member_id, true);
        }
        return $member_lavel;
    }
}

/**
 * Function: img_set_member_label()
 * Description: This function gets user id and set member_access_label
 * key if members keys are set true 
 * 
 * @param type $user_id = membership subscribed user id 
 * @uses $user_id - User id for identify
 * @since 1.0
 */
function img_set_member_label($user_id) {
    $membership = im_get_poducts();
    $member_key = array();
    foreach ($membership as $member) {
        $member_id = IM_MEMBER_ID . $member->PID;
        $data = get_user_meta($user_id, $member_id, true);
        if ($data)
            $member_key[] = $data;
    }
    //delete_user_meta($user_id, 'member_access_label');
    update_user_meta($user_id, 'member_access_label', $member_key);
}

function im_get_user_member_key($user_id) {
    $user_key = get_user_meta($user_id, 'member_access_label', true);
    return $user_key;
}

function im_payment_period($period, $period_cycle) {
    if ($period_cycle == "D") {
        if ($period > 1)
            return "Days";
        else
            return "Day";
    }
    elseif ($period_cycle == "W") {
        if ($period > 1)
            return "Weeks";
        else
            return "Week";
    }
    elseif ($period_cycle == "M") {
        if ($period > 1)
            return "Months";
        else
            return "Month";
    }
    elseif ($period_cycle == "Y") {
        if ($period > 1)
            return "Years";
        else
            return "Year";
    }
}

// pass strings in to clean
function inkthemes_clean($string) {
    $string = stripslashes($string);
    $string = trim($string);
    return $string;
}

function im_calc_subsperiod($period, $cycle) {
    $member_length = null;
    $period = (int) $period;
    if ($cycle != '') {
        switch ($cycle):
            case 'D':
                $member_length = $period;
                break;
            case 'W':
                $member_length = $period * 7;
                break;
            case 'M':
                $member_length = $period * 30;
                break;
            case 'Y':
                $member_length = $period * 365;
                break;
            case 'U':
                $member_length = 22 * 365;
                break;
        endswitch;
    }
    return $member_length;
}

/**
 * Used for set expiry duration of membership 
 * @global type $wpdb
 * @global type $im_tbl_expiry
 * @global type $wpdb
 * @global type $im_tbl_expiry
 * @param type $user_id
 * @param type $where
 * @param type $member_id
 */
function im_set_member_expiry($user_id, $where, $member_id) {
    $members = im_get_poducts_key($where);
    if ($members) {
        foreach ($members as $member) {

            if ($member->billing_option == 'one_time') {
                $subs_period = $member->subs_period;
                $subs_period_cycle = $member->subs_period_cycle;
                //Set expiry period for one_time payment

                /**
                 * Get subscription period length
                 */
                $member_length = im_calc_subsperiod($subs_period, $subs_period_cycle);

                /**
                 * Subscription duration
                 */
                $membership_duration = date_i18n('m/d/Y H:i:s', strtotime('+' . $member_length . ' days'));
                /**
                 * Add and update membership duration
                 */
                im_update_imduration($user_id, $member_id, $membership_duration);

                /**
                 * Update post meta 
                 */
                im_update_postmeta($user_id, $member_id, $where);

                /**
                 * Update expiry table
                 */
                im_update_expiry($user_id, $member_id, $where);
            }
            if ($member->billing_option == 'recurring') {
                $payment_period = $member->payment_period;
                $payment_period_cycle = $member->payment_period_cycle;

                /**
                 * Get subscription period length
                 */
                $first_billing = im_calc_subsperiod($payment_period, $payment_period_cycle);
                $member_length = $first_billing;
                //Values calculating for second period
                if ($member->trial_select == true) {
                    $trial_period = $member->trial_period;
                    $trial_period_cycle = $member->trial_period_cycle;
                    $second_billing = im_calc_subsperiod($trial_period, $trial_period_cycle);
                    $member_length = $first_billing + $second_billing;
                }
                /**
                 * Subscription duration
                 */
                $membership_duration = date_i18n('m/d/Y H:i:s', strtotime('+' . $member_length . ' days'));
                /**
                 * Add and update membership duration
                 */
                im_update_imduration($user_id, $member_id, $membership_duration);

                /**
                 * Update post meta 
                 */
                im_update_postmeta($user_id, $member_id, $where);

                /**
                 * Update expiry table
                 */
                im_update_expiry($user_id, $member_id, $where);
            }
        }
    }
}

function im_update_postmeta($user_id, $member_id, $where) {
    $exist = get_post_meta($user_id, $member_id, false);
    if ($exist == '') {
        add_post_meta($user_id, $member_id, $where);
    } else {
        update_post_meta($user_id, $member_id, $where);
    }
}

function im_update_expiry($user_id, $member_id, $member_value) {
    global $wpdb, $im_tbl_expiry;
    $is_expiry_exist = im_get_expiry_byMkey($member_id);
    $my_expiry['uid'] = $user_id;
    $my_expiry['meta_key'] = "im_membership";
    $my_expiry['member_key'] = $member_id;
    $my_expiry['member_value'] = $member_value;
    if ($is_expiry_exist == null) {
        $wpdb->insert($im_tbl_expiry, $my_expiry);
    } else {
        $value = array(
            'member_value' => $member_value,
        );
        $ex_where = array(
            'uid' => $user_id,
        );
        $wpdb->update($im_tbl_expiry, $value, $ex_where);
    }
}

function im_update_imduration($user_id, $member_id, $duration) {
    $old_duration = get_user_meta($user_id, 'im_member_duration_' . $member_id, true);
    update_user_meta($user_id, 'im_member_duration_' . $member_id, $duration, $old_duration);
}

/**
 * Used for showing expire time left
 * @param type $theTime
 * @return type
 */
function im_timeleft($theTime) {
    $now = strtotime("now");
    $timeLeft = $theTime - $now;

    $days_label = __('days', IM_SLUG);
    $day_label = __('day', IM_SLUG);
    $hours_label = __('hours', IM_SLUG);
    $hour_label = __('hour', IM_SLUG);
    $mins_label = __('mins', IM_SLUG);
    $min_label = __('min', IM_SLUG);
    $secs_label = __('secs', IM_SLUG);
    $r_label = __('remaining', IM_SLUG);
    $expired_label = __('Membership has expired', IM_SLUG);

    if ($timeLeft > 0) {
        $days = floor($timeLeft / 60 / 60 / 24);
        $hours = $timeLeft / 60 / 60 % 24;
        $mins = $timeLeft / 60 % 60;
        $secs = $timeLeft % 60;

        if ($days == 01) {
            $d_label = $day_label;
        } else {
            $d_label = $days_label;
        }
        if ($hours == 01) {
            $h_label = $hour_label;
        } else {
            $h_label = $hours_label;
        }
        if ($mins == 01) {
            $m_label = $min_label;
        } else {
            $m_label = $mins_label;
        }

        if ($days) {
            $theText = $days . " " . $d_label;
            if ($hours) {
                $theText .= ", " . $hours . " " . $h_label . " left";
            }
        } elseif ($hours) {
            $theText = $hours . " " . $h_label;
            if ($mins) {
                $theText .= ", " . $mins . " " . $m_label . " left";
            }
        } elseif ($mins) {
            $theText = $mins . " " . $m_label;
            if ($secs) {
                $theText .= ", " . $secs . " " . $secs_label . " left";
            }
        } elseif ($secs) {
            $theText = $secs . " " . $secs_label . " left";
        }
    } else {
        $theText = $expired_label;
    }
    return $theText;
}

/**
 * Used to check membsership expired
 * @param type $user_id
 * @param type $member_id
 * @return boolean
 */
function im_has_member_expired($user_id, $member_id) {

    $expire_date = get_user_meta($user_id, 'im_member_duration_' . $member_id, true);

    // debugging variables
    // echo date_i18n('m/d/Y H:i:s') . ' <-- current date/time GMT<br/>';
    // echo $expire_date . ' <-- expires date/time<br/>';
    // if current date is past the expires date, change post status to draft
    if ($expire_date) {
        if (strtotime(date('Y-m-d H:i:s')) > (strtotime($expire_date))) :
            $success = delete_user_meta($user_id, $member_id);
            return $success;
        else:
            return false;
        endif;
    }
}

/**
 * Used to expire or delete the user's membership when 
 * Ipn returns expired, canceled, refunded value.
 * @param type $user_id - user's id
 * @param type $member_id - member id 
 */
function im_member_expire($user_id, $member_id) {
    if ($member_id) {
        delete_user_meta($user_id, $member_id);
        update_user_meta($user_id, 'im_member_duration_' . $member_id, '');
    }
}

/**
 * Used for set default expiry
 * @global type $wpdb
 * @global type $im_tbl_product
 * @param type $post_id
 */
function im_set_default_expiry($post_id) {
    global $wpdb, $im_tbl_product;
    $sql = "SELECT * FROM $im_tbl_product WHERE package_type = 'pkg_free'";
    $QUERY = $wpdb->get_results($sql);
    foreach ($QUERY as $q) {
        $ad_length = $q->validity;
        if ($q->validity_per == 'D') {
            
        } elseif ($q->validity_per == 'M') {
            $ad_length = $ad_length * 30;
        } elseif ($q->validity_per == 'Y') {
            $ad_length = $ad_length * 365;
        }
    }
    if ($ad_length > 0) {
        $admin_ad_duration = date_i18n('m/d/Y H:i:s', strtotime('+' . $ad_length . ' days'));
        add_post_meta($post_id, 'im_listing_duration', $admin_ad_duration, true);
    }
}

/**
 * Used for checking expired membership 
 * @global type $wpdb
 * @global type $im_tbl_expiry
 */
function im_expiry() {
    global $wpdb, $im_tbl_expiry;
    $sql = "SELECT COUNT(*) FROM $im_tbl_expiry";
    $num_records = $wpdb->get_var($sql);
    $users_query = "SELECT * FROM $im_tbl_expiry WHERE RAND()*$num_records<20 ORDER BY RAND() LIMIT 0,80";
    $users_result = $wpdb->get_results($users_query);
    if (!empty($users_result)) {
        foreach ($users_result as $user) {
            //getting members status
            $expire = im_has_member_expired($user->uid, $user->member_key);
            //if member expired
            if ($expire === true) {
                $site_name = get_option('blogname');
                $email = get_option('admin_email');
                $login_url = site_url("/wp-login.php?action=login");
                $user_name = get_the_author_meta('user_login', $user->uid);
                $message .= "--------------------------------------------------------------------------------\r";
                $message .= sprintf(__('Dear %s', IM_SLUG), $user_name) . " \r";
                $message .= __('Your membership has been expired, ', IM_SLUG) . " \r";
                $message .= sprintf(__("Login On: %s"), $login_url) . " \r";
                $message .= "--------------------------------------------------------------------------------\r";
                $message = __($message, IM_SLUG);
                //get member author email
                $to = get_the_author_meta('user_email', $user->uid);
                $subject = __('Membership expiration notice', IM_SLUG);
                $headers = 'From: Site Admin <' . $email . '>' . "\r\n" . 'Reply-To: ' . $email;
                wp_mail($to, $subject, $message, $headers);
            }
        }
    }
}

add_action('init', 'im_expiry');
