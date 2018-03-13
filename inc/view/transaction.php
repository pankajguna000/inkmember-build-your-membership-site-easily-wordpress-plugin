<div id="inkmember_wrap" style="width:950px;">
    <div class="member_head"><div id="icon-options-general" class="icon32">	
        </div>
        <h2><?php _e('InkMember Transaction', 'inkmember'); ?></h2></div>
    <div id="add_form">
        <table>
            <thead>
                <tr>
                    <th><?php _e(__('User Name', 'inkmember'), IM_SLUG); ?></th>
                    <th><?php _e(__('Transaction Title', 'inkmember'), IM_SLUG); ?></th>
                    <th><?php _e(__('Transaction ID', 'inkmember'), IM_SLUG); ?></th>
                    <th> <?php _e(__('Transaction Type', 'inkmember'), IM_SLUG); ?></th>
                    <th> <?php _e(__('Payment Date', 'inkmember'), IM_SLUG); ?></th>
                    <th> <?php _e(__('Payer Email', 'inkmember'), IM_SLUG); ?></th>
                    <th> <?php _e(__('Action', 'inkmember'), IM_SLUG); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $im_transaction, $wpdb;
                $query = "SELECT * FROM $im_transaction";
                $values = $wpdb->get_results($query);
                if ($values) {
                    foreach ($values as $value) {
                        $user_login = get_the_author_meta('user_login', $value->uid);

                        if ($value->txn_type == 'web_accept')
                            $txn_type = 'One Time';

                        elseif ($value->txn_type == 'subscr_pay' || $value->txn_type == 'subscr_payment')
                            $txn_type = 'Recurring';
                        echo '<tr><td>' . $user_login . '</td><td>' . $value->item_name . '</td><td>' . $value->txn_id . '</td><td>' . $txn_type . '</td><td>' . $value->payment_date . '</td><td>' . $value->payer_email . '</td><td><a href="' . admin_url('/admin.php?page=transation&id=' . $value->id) . '"><img src="' . plugins_url('../images/delete.png', __DIR__) . '"/></a></td></tr>';
                    }
                } else {
                    echo "<tr><td colspan='7'>" . __("You don't have any transaction yet", "inkmember") . "</td></tr>";
                }
                ?>
            </tbody>            
        </table>
    </div>
</div>