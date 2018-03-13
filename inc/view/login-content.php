<div class="login_content">
    <?php
    if (!is_user_logged_in()) {
        require_once dirname(__FILE__) . '/login-css.php';
        echo im_autho_form();
    }
    ?>
    <a href="<?php echo site_url(); ?>"><?php _e('Back to site?', IM_SLUG); ?></a>
</div></body></html>