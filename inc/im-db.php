<?php

global $wpdb;
$collate = '';
if ($wpdb->has_cap('collation')) {
    if (!empty($wpdb->charset))
        $collate = "DEFAULT CHARACTER SET $wpdb->charset";
    if (!empty($wpdb->collate))
        $collate .= " COLLATE $wpdb->collate";
}

global $wpdb, $im_tbl_product;
$tblprefix = $wpdb->prefix;
$im_tbl_product = $tblprefix . 'im_products';

if ($wpdb->get_var("SHOW TABLES LIKE \"$im_tbl_product\"") != $im_tbl_product) {
    $product = "CREATE TABLE  `$im_tbl_product` (
    `PID` INT( 8 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `product_name` VARCHAR( 100 ) NOT NULL ,
    `paypal_email` VARCHAR( 100 ) NOT NULL ,
    `billing_option` VARCHAR( 50 ) NOT NULL ,
    `p_button_img` VARCHAR( 1000 ) NOT NULL COMMENT  'payment button image',
    `j_button_img` VARCHAR( 1000 ) NOT NULL COMMENT  'free join button image',
    `currency` VARCHAR( 10 ) NOT NULL ,
    `product_price` FLOAT( 100,2 ) NOT NULL ,
    `payment_period` VARCHAR( 10 ) NOT NULL ,
    `payment_period_cycle` VARCHAR( 10 ) NOT NULL ,
    `no_of_payment` INT( 10 ) NOT NULL ,
    `trial_select` INT( 10 ) NOT NULL ,
    `trial_price` FLOAT( 100,2 ) NOT NULL ,
    `trial_period` VARCHAR( 10 ) NOT NULL ,
    `trial_period_cycle` VARCHAR( 10 ) NOT NULL ,
    `subs_period` FLOAT( 100,2 ) NOT NULL ,
    `subs_period_cycle` VARCHAR( 10 ) NOT NULL ,
    `mailling_list_type` VARCHAR( 50 ) NOT NULL ,
    `subs_email` VARCHAR( 100 ) NOT NULL,
    `member_key` VARCHAR( 100 ) NOT NULL
    ) ENGINE = INNODB;";
    $wpdb->query($product);
} else {
    if (!get_option('ink_is_column_altered')) {
        $wpdb->query("ALTER TABLE $im_tbl_product MODIFY product_price FLOAT(100,2);");
        $wpdb->query("ALTER TABLE $im_tbl_product MODIFY trial_price FLOAT(100,2);");
        $wpdb->query("ALTER TABLE $im_tbl_product MODIFY subs_period FLOAT(100,2);");
        update_option('ink_is_column_altered', 'altered');
    }
}

global $im_transaction;
$im_transaction = $tblprefix . 'im_order_info';

// create the paypal transaction table

$sql = "CREATE TABLE IF NOT EXISTS " . $im_transaction . " (
        `id` mediumint(9) NOT NULL AUTO_INCREMENT,
        `uid` INT(10) NOT NULL,
        `first_name` varchar(100) NOT NULL DEFAULT '',
        `last_name` varchar(100) NOT NULL DEFAULT '',
        `payer_email` varchar(100) NOT NULL DEFAULT '',
        `street` varchar(255) NOT NULL DEFAULT '',
        `city` varchar(255) NOT NULL DEFAULT '',
        `state` varchar(255) NOT NULL DEFAULT '',
        `zipcode` varchar(100) NOT NULL DEFAULT '',
        `residence_country` varchar(255) NOT NULL DEFAULT '',
        `transaction_subject` varchar(255) NOT NULL DEFAULT '',
        `memo` varchar(255) DEFAULT NULL,
        `item_name` varchar(255) DEFAULT NULL,
        `item_number` varchar(255) DEFAULT NULL,
        `quantity` char(10) DEFAULT NULL,
        `payment_type` varchar(50) NOT NULL DEFAULT '',
        `payer_status` varchar(50) NOT NULL DEFAULT '',
        `payer_id` varchar(50) NOT NULL DEFAULT '',
        `receiver_id` varchar(50) NOT NULL DEFAULT '',
        `parent_txn_id` varchar(30) NOT NULL DEFAULT '',
        `txn_id` varchar(30) NOT NULL DEFAULT '',
        `txn_type` varchar(10) NOT NULL DEFAULT '',
        `payment_status` varchar(50) NOT NULL DEFAULT '',
        `pending_reason` varchar(50) DEFAULT NULL,
        `mc_gross` varchar(10) NOT NULL DEFAULT '',
        `mc_fee` varchar(10) NOT NULL DEFAULT '',
        `tax` varchar(10) DEFAULT NULL,
        `exchange_rate` varchar(25) DEFAULT NULL,
        `mc_currency` varchar(20) NOT NULL DEFAULT '',
        `reason_code` varchar(20) NOT NULL DEFAULT '',
        `custom` varchar(255) NOT NULL DEFAULT '',
        `test_ipn` varchar(20) NOT NULL DEFAULT '',
        `payment_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `create_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY id (`id`)) $collate;";

$wpdb->query($sql);
if (!get_option('is_tt_alter')) {
    $wpdb->query("ALTER TABLE $im_transaction MODIFY txn_type varchar(100);");
    update_option('is_tt_alter', 'yes');
}

global $wpdb, $im_tbl_expiry;
$im_tbl_expiry = $tblprefix . 'im_expiry';
if ($wpdb->get_var("SHOW TABLES LIKE \"$im_tbl_expiry\"") != $im_tbl_expiry) {
    $expiry = "CREATE TABLE `$im_tbl_expiry` (
    `ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `uid` INT( 10 ) NOT NULL ,
    `meta_key` VARCHAR( 100 ) NOT NULL ,
    `member_key` VARCHAR( 100 ) NOT NULL ,
    `member_value` VARCHAR( 1000 ) NOT NULL
   ) $collate;
   ";
    $wpdb->query($expiry);
}

function im_get_poducts() {
    global $wpdb, $im_tbl_product;
    $results = $wpdb->get_results("SELECT * FROM $im_tbl_product");
    return $results;
}

function im_get_poducts_key($where) {
    global $wpdb, $im_tbl_product;
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $im_tbl_product WHERE member_key = %s", $where));
    return $results;
}

function im_delete_trans($id) {
    global $wpdb, $im_transaction;
    $wpdb->query($wpdb->prepare('DELETE FROM ' . $im_transaction . ' WHERE id = %d', $id));
}

function im_get_expiry_byMkey($member_key) {
    global $wpdb, $im_tbl_expiry;
    $sql = "SELECT COUNT(*) FROM $im_tbl_expiry WHERE member_key = $member_key";
    $num_records = $wpdb->get_var($sql);
    if ($num_records > 0) {
        return $num_records;
    } else {
        return;
    }
}
