<div id="add_form">
    <table>
        <thead>
            <tr>
                <th><?php _e(__('Product Name', 'inkmember'), IM_SLUG); ?></th>
                <th><?php _e(__('Billing Type', 'inkmember'), IM_SLUG); ?></th>
                <th><?php _e(__('Action', 'inkmember'), IM_SLUG); ?></th>
                <th><?php _e(__('Purchase Link', 'inkmember'), IM_SLUG); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $results = im_get_poducts();
            $str = '';
            if ($results) {
                foreach ($results as $result) {
                    if ($result->billing_option == 'one_time')
                        $billing_option = __("One Time", "inkmember");
                    elseif ($result->billing_option == "recurring")
                        $billing_option = __("Recurring", 'inkmember');
                    elseif ($result->billing_option == "free_mem")
                        $billing_option = __("Free Member", 'inkmember');
                    $str .= '<tr>';
                    $str .= '<td style="width:150px;">' . $result->product_name . '</td>'
                            . '<td style="width:74px;">' . $billing_option . '</td>
                                <td style="width:74px;"><a class="edit" title="' . __('Edit', 'inkmember') . '" href="' . admin_url('/admin.php?page=inkmember&action=edit&pid=' . $result->PID) . '"><img src="' . plugins_url('../images/edit.png', __DIR__) . '"/></a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a onclick="return confirm("' . __('Click OK to reset. Any settings will be lost!', 'inkmember') . '");" class=delete title="' . __('Delete', 'inkmember') . '" href="' . admin_url('/admin.php?page=inkmember&action=delete&pid=' . $result->PID) . '"><img src="' . plugins_url('../images/delete.png', __DIR__) . '"/></a>';
                    $str .= '</td>'
                            . '<td><strong>' . site_url('/?imaction=membership&amp;purchase_key=' . $result->member_key) . '</strong></td>'
                            . '</tr>';
                }
                echo $str;
            }else {
                echo "<tr><td colspan=\"4\">" . __("You don't have any membership. Please create a membership", "inkmember") . "</td></tr>";
            }
            ?>
        </tbody>            
    </table>
</div>