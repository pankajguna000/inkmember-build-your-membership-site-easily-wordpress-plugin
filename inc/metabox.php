<?php

function im_metabox() {
    if (function_exists('add_meta_box')) {

        $post_types = get_post_types('', 'names');
        foreach ($post_types as $post_type) {
            if ($post_type !== 'attachment' && $post_type !== 'nav_menu_item')
                add_meta_box('im-meta', __("InkMember Membership", "inkmember"), 'im_meta', $post_type, 'normal', 'high');
        }
    }
}

add_action('add_meta_boxes', 'im_metabox');

function im_meta() {
    $membership = im_get_poducts();
    global $post;
    ?>
    <div class="form-wrap">
        <?php
        wp_nonce_field(plugin_basename(__FILE__), 'member_wpnonce', false, true);

        foreach ($membership as $member) {
            $member_id = IM_MEMBER_ID . $member->PID;
            $data = get_post_meta($post->ID, $member_id, true);
            ?>
            <div class="form-required"> 
                <label for="<?php echo $member_id; ?>" class="check-label"><input style="float:left; margin:2px 0 5px 0;" id="<?php echo $member_id; ?>" type="checkbox" name="<?php echo $member_id; ?>" value="<?php echo $member->member_key; ?>" 
                    <?php
                    if ($data) {
                        echo 'checked="checked"';
                    }
                    ?> />&nbsp;&nbsp;
                    <?php echo $member->product_name; ?>&nbsp;&nbsp;&nbsp;&nbsp;<span style="float:right; display: inline-block;">Id: <?php echo $member->PID; ?></span></label>				
                <div class="clear"></div>
            </div>
        <?php } ?>
        <p clas="des"><?php _e("Note: You have to use above level's id for protect your content by shortcode.", "inkmember"); ?></p>
    </div>
    <?php
}

function im_save_meta_box($post_id) {
    global $post;
    if (!wp_verify_nonce($_POST['member_wpnonce'], plugin_basename(__FILE__)))
        return $post_id;
    if (!current_user_can('edit_post', $post_id))
        return $post_id;
    $membership = im_get_poducts();
    foreach ($membership as $member) {
        $member_id = IM_MEMBER_ID . $member->PID;
        update_post_meta($post_id, $member_id, $_POST[$member_id]);
    }
}

add_action('save_post', 'im_save_meta_box');


add_action('show_user_profile', 'im_user_profile_fields');
add_action('edit_user_profile', 'im_user_profile_fields');

function im_user_profile_fields($user) {
    ?>
    <h3><?php _e("Membership Option", IM_SLUG); ?></h3>

    <table class="form-table" style="width:500px;">
        <tr>
            <th><label><?php _e("Membership Level", IM_SLUG); ?></label></th>
            <th><label><?php _e("Expiry On", IM_SLUG); ?></label></th>
        </tr>
        <?php
        wp_nonce_field(plugin_basename(__FILE__), 'member_wpnonce', false, true);
        $membership = im_get_poducts();
        if ($membership) {
            foreach ($membership as $member) {
                $member_id = IM_MEMBER_ID . $member->PID;
                ?>
                <tr id="<?php echo $member_id; ?>">
                    <td>
                        <?php
                        $data = get_user_meta($user->ID, $member_id, true);
                        if ($data)
                            $checked = 'checked="checked"';
                        else
                            $checked = '';
                        ?>
                        <div class="form-required"> 
                            <label for="<?php echo $member_id; ?>" class="check-label"><?php if (current_user_can('administrator')) { ?><input style="float:left;" id="<?php echo $member_id; ?>" <?php if ($checked) echo $checked; ?> type="checkbox" name="<?php echo $member_id; ?>" value="<?php echo $member->member_key; ?>"/>&nbsp;&nbsp;<?php } ?>
                                <?php echo $member->product_name; ?></label>				
                            <div class="clear"></div>
                        </div>

                    </td>
                    <td>
                        <?php
                        $member_expiry = get_user_meta($user->ID, 'im_member_duration_' . IM_MEMBER_ID . $member->PID, true);
                        if ($member_expiry)
                            echo im_timeleft(strtotime($member_expiry));
                        ?>
                    </td>
                </tr>
                <?php
            }
        }
        ?>        

    </table>
    <?php
}

add_action('personal_options_update', 'im_save_user_profile_fields');
add_action('edit_user_profile_update', 'im_save_user_profile_fields');

function im_save_user_profile_fields($user_id) {

    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    $membership = im_get_poducts();
    $member_ship = array();
    if ($membership) {
        foreach ($membership as $member) {
            $member_id = IM_MEMBER_ID . $member->PID;

            update_user_meta($user_id, $member_id, $_POST[$member_id]);
            $member_ship[] = $_POST[$member_id];
            if ($_POST[$member_id] != '') {
                im_set_member_expiry($user_id, $member->member_key, $member_id);
            } else {
                im_member_expire($user_id, $member_id);
            }
        }
    }
    update_user_meta($user_id, 'member_access_label', $member_ship);
}
