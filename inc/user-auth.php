<?php

function im_auth_setup() {
    if (!get_option('users_can_register')) {
        update_option('users_can_register', true);
    }
}

add_action('admin_init', 'im_auth_setup');

global $pagenow;
// check to prevent php "notice: undefined index" msg
if (isset($_GET['action'])) {
    $theaction = $_GET['action'];
} else {
    $theaction = '';
}
// if the user is on the login page, then let the site begin
if ($pagenow == 'wp-login.php' && $theaction != 'logout' && !isset($_GET['key'])) :
    add_action('init', 'im_login_init');
endif;

function im_login_init() {
    //nocache_headers(); //cache clear
    global $pagenow;
    $page_id = get_option('im_login');
    if (isset($_REQUEST['action'])) :
        $action = $_REQUEST['action'];
    elseif (isset($_REQUEST['loggedout'])):
        $action = $_REQUEST['loggedout'];
    else :
        $action = 'login';
    endif;
    switch ($action) :
        case 'lostpassword' :
        case 'retrievepassword' :
            im_show_password();
            break;
        case 'true':
            wp_redirect(home_url('?page_id=' . $page_id));
            break;
        case 'register':
        default:
            im_show_login();
            break;
    endswitch;
    exit;
}

function im_autho_form($display = null, $class = null, $memredirect = false) {
    if (is_user_logged_in()) {
        echo("<script>location.href = '" . esc_url(site_url('/')) . "';</script>");
    } else {
        $form = '';
        $form = <<<EOF
    <div class="$class" style="display:$display;" id="im_authform">
        <a id="close_x" class="close sprited" href="#"></a>        
EOF;
        $form .= im_loginform($memredirect);
        $form .= '<table><tr><td>';
        $form .= im_reg_form($memredirect);
        $form .= '</td></tr></table>';
        $form .= '</div>';
        return $form;
    }
}

function im_login_proceed_form() {

    global $posted, $post;
    $page_id = get_option('im_login');
    if (isset($_REQUEST['redirect_to']))
        $redirect_to = $_REQUEST['redirect_to'];
    elseif (isset($_REQUEST['redirect_to_buy']))
        $redirect_to = $_REQUEST['redirect_to_buy'];
    else
        $redirect_to = admin_url();
    if (is_ssl() && force_ssl_login() && !force_ssl_admin() && (0 !== strpos($redirect_to, 'https')) && (0 === strpos($redirect_to, 'http')))
        $secure_cookie = false;
    else
        $secure_cookie = '';
    $creds = array();
    $creds['user_login'] = sanitize_user($_POST['username']);
    $creds['user_password'] = $_POST['password'];
    $creds['remember'] = false;
    $user = wp_signon('', $secure_cookie);
    $redirect_to = apply_filters('login_redirect', $redirect_to, isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '', $user);
    if (!is_wp_error($user)) {
        if (user_can($user, 'manage_options')) :
            $redirect_to = admin_url();
        endif;
        if ($page_id == $_POST['post_id']) {
            $redirect_to = site_url();
        }
        wp_safe_redirect($redirect_to);
        exit;
    }
    $errors = $user;
    return $errors;
}

function im_reg_proceed_form($success_redirect = '', $memredirect = false) {
    if (isset($_REQUEST['redirect_to']))
        $redirect_to = $_REQUEST['redirect_to'];
    elseif (isset($_REQUEST['redirect_to_buy']))
        $redirect_to = $_REQUEST['redirect_to_buy'];
    else
        $redirect_to = admin_url();

    if (!$success_redirect)
        $success_redirect = site_url();
    if (get_option('users_can_register')) :

        global $posted;

        $posted = array();
        $errors = new WP_Error();
        if (isset($_POST['register']) && $_POST['register']) {
// Get (and clean) data
            $fields = array(
                'your_username',
                'your_email',
                'your_password',
                'your_password_2'
            );
            foreach ($fields as $field) {
                $posted[$field] = stripslashes(trim($_POST[$field]));
            }

            $user_login = sanitize_user($posted['your_username']);
            $user_email = apply_filters('user_registration_email', $posted['your_email']);

// Check the username
            if ($posted['your_username'] == '')
                $errors->add('empty_username', __('<strong>ERROR</strong>: Please enter a username.', IM_SLUG));
            elseif (!validate_username($posted['your_username'])) {
                $errors->add('invalid_username', __('<strong>ERROR</strong>: This username is invalid.  Please enter a valid username.', IM_SLUG));
                $posted['your_username'] = '';
            } elseif (username_exists($posted['your_username']))
                $errors->add('username_exists', __('<strong>ERROR</strong>: This username is already registered, please choose another one.', IM_SLUG));

// Check the e-mail address
            if ($posted['your_email'] == '') {
                $errors->add('empty_email', __('<strong>ERROR</strong>: Please type your e-mail address.', IM_SLUG));
            } elseif (!is_email($posted['your_email'])) {
                $errors->add('invalid_email', __('<strong>ERROR</strong>: The email address isn&#8217;t correct.', IM_SLUG));
                $posted['your_email'] = '';
            } elseif (email_exists($posted['your_email']))
                $errors->add('email_exists', __('<strong>ERROR</strong>: This email is already registered, please choose another one.', IM_SLUG));

// Check Passwords match
            if ($posted['your_password'] == '')
                $errors->add('empty_password', __('<strong>ERROR</strong>: Please enter a password.', IM_SLUG));
            elseif ($posted['your_password_2'] == '')
                $errors->add('empty_password', __('<strong>ERROR</strong>: Please enter password twice.', IM_SLUG));
            elseif ($posted['your_password'] !== $posted['your_password_2'])
                $errors->add('wrong_password', __('<strong>ERROR</strong>: Passwords do not match.', IM_SLUG));

            $is_captcha = get_option('im_recaptcha');
            if ($is_captcha && $is_captcha === 'on') {
                $recaptcha = $_POST['g-recaptcha-response'];
                if (!empty($recaptcha)) {
                    $secret = get_option('im_recaptcha_private');
                    $secret = empty($secret) ? 'Google secret key' : $secret;
                    $captcha_data = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $secret . "&response=" . $_POST['g-recaptcha-response']);
                    $response = json_decode($captcha_data, TRUE);
                    if ($response['success']) {
                        $captcha_details = true;
                    } else {
                        $captcha_details = false;
                        $error = array_search("invalid-input-secret", $response['error-codes']);
                        if ($error == 0) {
                            $errors->add('wrong_captcha_key', __('<strong>ERROR</strong>: Please enter correct reCAPTCHA key.', IM_SLUG));
                        } else {
                            $errors->add('wrong_captcha', __('<strong>ERROR</strong>: Please re-enter your reCAPTCHA.', IM_SLUG));
                        }
                    }
                } else {
                    $captcha_details = false;
                    $errors->add('wrong_captcha', __('<strong>ERROR</strong>: Please re-enter your reCAPTCHA.', IM_SLUG));
                }
            }


            do_action('register_post', $posted['your_username'], $posted['your_email'], $errors);
            $errors = apply_filters('registration_errors', $errors, $posted['your_username'], $posted['your_email']);

            if (!$errors->get_error_code()) {
                $user_pass = $posted['your_password'];
                $user_id = wp_create_user($posted['your_username'], $user_pass, $posted['your_email']);
                if (!$user_id) {
                    $errors->add('registerfail', sprintf(__('<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the webmaster</a> ', IM_SLUG), get_option('admin_email')));
                    return array('errors' => $errors, 'posted' => $posted);
                }

// Change role
                wp_update_user(array('ID' => $user_id, 'role' => 'subscriber'));

                wp_new_user_notification($user_id, $user_pass);

                $secure_cookie = is_ssl() ? true : false;

                wp_set_auth_cookie($user_id, true, $secure_cookie);

### Redirect
                if (user_can($user_id, 'manage_options')) :
                    $redirect_to = admin_url();
                endif;
                if ($page_id == $_POST['post_id']) {
                    $redirect_to = home_url();
                }
                wp_safe_redirect($redirect_to);
                exit;
            } else {
                return array('errors' => $errors, 'posted' => $posted);
            }
        }
    endif;
}

function im_show_login() {
    global $posted, $errors;
    if (isset($_POST['register'])) {
        $result = im_reg_proceed_form();

        $errors = $result['errors'];
        $posted = $result['posted'];
    } elseif (isset($_POST['wp-submit'])) {
        $errors = im_login_proceed_form();
    }

// Clear errors if loggedout is set.
    if (!empty($_GET['loggedout']))
        $errors = new WP_Error();

// If cookies are disabled we can't log in even with a valid user+pass
    if (isset($_POST['testcookie']) && empty($_COOKIE[TEST_COOKIE]))
        $errors->add('test_cookie', TEST_COOKIE);

    if (isset($_GET['loggedout']) && TRUE == $_GET['loggedout'])
        $notify = __("You are now logged out.", IM_SLUG);

    elseif (isset($_GET['registration']) && 'disabled' == $_GET['registration'])
        $errors->add('registerdisabled', __("User registration is currently not allowed.", IM_SLUG));

    elseif (isset($_GET['checkemail']) && 'confirm' == $_GET['checkemail'])
        $notify = __("Check your email for the confirmation link.", IM_SLUG);

    elseif (isset($_GET['checkemail']) && 'newpass' == $_GET['checkemail'])
        $notify = __("Check your email for your new password.", IM_SLUG);

    elseif (isset($_GET['checkemail']) && 'registered' == $_GET['checkemail'])
        $notify = __("Registration complete. Please check your e-mail.", IM_SLUG);
    if (is_user_logged_in()) {
        wp_redirect(site_url());
        exit();
    }
    if (is_user_logged_in()) {
        global $wpdb, $current_user;
        $userRole = ($current_user->data->wp_capabilities);
        $role = key($userRole);
        unset($userRole);
        $edit_anchr = '';
        switch ($role) {
            case ('administrator' || 'editor' || 'contributor' || 'author'):
                break;
            default:
                break;
        }
    }
    $heardoc = '';
    $heardoc .='<html>';
    $heardoc .='<head>';
    $heardoc .='<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    $heardoc .='<title>WordPress &rsaquo; Notification</title>';
    $heardoc .='</head>';
    $heardoc .='<body id="error-page">';
    echo trim($heardoc);
    echo '<link rel="stylesheet" href="' . plugins_url('css/error.css', __DIR__) . '"/>';
    if (isset($notify) && !empty($notify)) {
        echo '<p class="success">' . $notify . '</p>';
    }
//Showing login or register error
    if (isset($errors) && sizeof($errors) > 0 && $errors->get_error_code()) :
        echo '<ul id="error" class="error">';
        foreach ($errors->errors as $error) {
            echo '<li>' . $error[0] . '</li>';
        }
        echo '</ul>';
    endif;
    im_notify_footer();
}

function im_login_content() {
    if ($overridden_template = locate_template('template-login.php')) {
        // locate_template() returns path to file
        // if either the child theme or the parent theme have overridden the template
        load_template($overridden_template);
    }
}

function im_show_password() {
    $errors = new WP_Error();

    if (isset($_POST['user_login']) && $_POST['username']) {
        $errors = retrieve_password();

        if (!is_wp_error($errors)) {
            wp_redirect('wp-login.php?checkemail=confirm');
            exit();
        }
    }

    if (isset($_GET['error']) && 'invalidkey' == $_GET['error'])
        $errors->add('invalidkey', "Sorry, that key does not appear to be valid.");

    do_action('lost_password');
    do_action('lostpassword_post');

    if (isset($notify) && !empty($notify)) {
        echo '<p class="success">' . $notify . '</p>';
    }
    if ($errors && sizeof($errors) > 0 && $errors->get_error_code()) :
        echo '<ul class="error">';
        foreach ($errors->errors as $error) {
            echo '<li>' . $error[0] . '</li>';
        }
        echo '</ul>';
    endif;
    im_notify_head();
    im_lost_pw();
    im_notify_footer();
}

function im_loginform($memredirect = false) {
    $form = '';
    $action = $_SERVER['PHP_SELF'];
    $form .= '<div id="login_form">';
    $form .= '<form name="loginform" id="loginform" action="' . esc_url(site_url('wp-login.php', 'login_post')) . '" method="post">';
    $form .= '<h1 class="form_tag">' . __('Sign In', IM_SLUG) . '</h1>';
    $form .= '<div class="label">';
    $form .= '<label for="username">' . __('User Name:', IM_SLUG) . '<span class="required">*</span></label>';
    $form .= '</div>';
    $form .= '<div class="row">';
    $form .= '<input type="text" name="log" id="username" value=""/>';
    $form .= '</div>';
    $form .= '<div class="label">';
    $form .= '<label for="password">' . __('Password:', IM_SLUG) . '<span class="required">*</span></label>';
    $form .= '</div>';
    $form .= '<div class="row">';
    $form .= '<input type="password" name="pwd" id="password"/>';
    $form .= '</div>';
    $form .= '<input class="submit" type="submit" name="wp-submit" value="' . __('Log In', IM_SLUG) . '"/>';
    if ($memredirect) {
        $form .= '<input class="redirect_to" type="hidden" name="redirect_to_buy" value="' . $_SERVER['REQUEST_URI'] . '" />';
    } else {
        $form .= '<input class="redirect_to" type="hidden" name="redirect_to" value="' . $_SERVER['REQUEST_URI'] . '" />';
    }
    if (!empty($post)) {
        $form .= '<input type="hidden" name="post_id" value="' . $post->ID . '" />';
    }
    $form .= '<input type="hidden" name="user-cookie" value="1" />';
    $form .= '</form>';
    $form .= '</div> ';
    return $form;
}

function im_reg_form($memredirect = false) {
    global $posted, $post;
    $form = '';
    if (get_option('users_can_register')) :
        $action = home_url('wp-login.php?action=register');
        $form .= '<div id="register"><script type="text/javascript" src="https://www.google.com/recaptcha/api.js"></script>';
        $form .= '<form id="register" class="im_register_form" name="registration" action="' . $action . '" method="post">';
        $form .= '<h1 class="form_tag">' . __('Create an Account', IM_SLUG) . '</h1>';
        $form .= '<div class="label">';
        $form .= '<label for="username">' . __('User Name:', IM_SLUG) . '<span class="required">*</span></label>';
        $form .= '</div>';
        $form .= '<div class="row">';
        $form .= '<input type="text" name="your_username" id="reg_username" value=""/>';
        $form .= '</div>';
        $form .= '<div class="label">';
        $form .= '<label for="email">' . __('Email:', IM_SLUG) . '<span class="required">*</span></label>';
        $form .= '</div>';
        $form .= '<div class="row">';
        $form .= '<input type="text" name="your_email" id="reg_email" value=""/>';
        $form .= '<p class="user_email_error"></p>';
        $form .= '</div>';
        $form .= '<div class="label">';
        $form .= '<label for="password1">' . __('Enter Password:', IM_SLUG) . '<span class="required">*</span></label>';
        $form .= '</div>';
        $form .= '<div class="row">';
        $form .= '<input type="password" name="your_password" id="reg_password1" value=""/>';
        $form .= '</div>';
        $form .= '<div class="label">';
        $form .= '<label for="password2">' . __('Enter Password Again', IM_SLUG) . '<span class="required">*</span></label>';
        $form .= '</div>';
        $form .= '<div class="row">';
        $form .= '<input type="password" name="your_password_2" id="reg_password2" value=""/>';
        $form .= '<p class="perror"></p>';
        $form .= '</div>';
        $form .= '<div class="row">';
        $captch_public = get_option('im_recaptcha_public');
        if ($captch_public == '') {
            $captch_public = 'Google Public Key';
        }
        $is_captcha_on = get_option('im_recaptcha');
        if ($is_captcha_on === 'on') {
            $form .= '<div class="g-recaptcha-div"><div class="g-recaptcha" data-sitekey="' . $captch_public . '"></div></div>';
        }
        $form .= '<p class="captch_error"></p>';
        $form .= '</div>';
        $form .= '<input type="submit" name="register" value="' . __('Register', IM_SLUG) . '" class="submit" tabindex="103" />';
        if (!empty($post)) {
            if ($post->ID != get_option('im_login')) {
                if ($memredirect) {
                    $form .= '<input class="redirect_to" type="hidden" name="redirect_to_buy" value="' . $_SERVER['REQUEST_URI'] . '" />';
                } else {
                    $form .= '<input class="redirect_to" type="hidden" name="redirect_to" value="' . $_SERVER['REQUEST_URI'] . '" />';
                }
                $form .= '<input type="hidden" name="post_id" value="' . $post->ID . '" />';
            }
        }
        $form .= '<input type="hidden" name="user-cookie" value="1" />';
        $form .= '</form>';
        $form .= '</div>';
        $script = <<<EOF
                <script type="text/javascript">
           
        </script>
EOF;
        return $form . $script;
    endif;
}

function im_lost_pw() {
    ?>
    <div id="fotget_pw">
        <h3><?php _e("Forgot your password?", IM_SLUG); ?></h3>

        <form method="post" action="<?php echo site_url('wp-login.php?action=lostpassword', 'login_post') ?>"
              class="wp-user-form">
            <div class="row">
                <label for="user_login" class="hide"><?php _e("Enter your email or username.", IM_SLUG); ?>: </label><br/>
                <input type="text" name="user_login" value="" size="20" id="user_login"/>
            </div>
            <div class="row">
                <?php do_action('login_form', 'resetpass'); ?>
                <input type="submit" name="user-submit" value="<?php _e("Reset my password", IM_SLUG); ?>" class="user-submit"/>
                <?php
                $reset = $_GET['reset'];
                if ($reset == true) {
                    echo '<p>' . __('A message will be sent to your email address.', IM_SLUG) . '</p>';
                }
                ?>
                <input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>?reset=true"/>
                <input type="hidden" name="user-cookie" value="1"/>
            </div>
        </form>
    </div>
    <?php
}
