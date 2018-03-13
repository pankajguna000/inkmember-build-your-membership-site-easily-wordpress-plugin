
jQuery(function() {
    jQuery('.buy_btn').each(function() {
        var clicked = jQuery(this).attr('id');
        jQuery('#' + clicked).click(function(e) {
            jQuery("#im_authform").lightbox_me({
                centered: true,
                onLoad: function() {
                    jQuery("#im_authform").find("input:first").focus();
                    jQuery('.redirect_to').val(jQuery('#' + clicked).data('requrl'));
                    jQuery(".redirect_to").val(jQuery(".buy_btn").attr("href"));
                }
            });
            e.preventDefault();
        });
    });
});
jQuery(document).ready(function() {

    var username = jQuery("#reg_username");
    function validate_username() {
        if (username.val() == '') {
            username.addClass('error');
            username.css('border', 'solid 1px red');
            username.attr("placeholder", "Required");
            return false;
        } else {
            username.removeClass('error');
            username.css("border", "1px solid #c3c3c3");
            return true;
        }
    }
    username.blur(validate_username);
    username.keyup(validate_username);
    var reg_email = jQuery('#reg_email');
    var user_email_error = jQuery('.user_email_error');
    function validate_email() {
        if (reg_email.val() == "")

        {
            reg_email.addClass('error');
            reg_email.css('border', 'solid 1px red');
            reg_email.attr("placeholder", "Required");
            return false;
        }
        else if (reg_email.val() != "")
        {
            var a = reg_email.val();
            var reg = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
            if (reg_email.val() == "") {
                reg_email.addClass("error");
                user_email_error.text("Please provide your email address");
                user_email_error.addClass("error");
                return false;
            } else if (reg.test(reg_email.val()) == false) {
                reg_email.addClass("error");
                user_email_error.text("Please provide valid email address");
                user_email_error.addClass("error");
                return false;
            } else {
                reg_email.removeClass("error");
                user_email_error.text("");
                user_email_error.removeClass("error");
                reg_email.css("border", "1px solid #c3c3c3");
                return true;
            }


        } else
        {
            reg_email.removeClass("error");
            user_email_error.text("");
            user_email_error.removeClass("error");
            return true;
        }
    }
    reg_email.blur(validate_email);
    reg_email.keyup(validate_email);

    var pass1 = jQuery('#reg_password1');
    var pass2 = jQuery('#reg_password2');
    var error = jQuery('.perror');
    function validate_password() {
        if (pass1 != pass2) {
            error.addClass('error');
            error.text("Password does not match!");
            return false;
        } else {
            error.removeClass('error');
            return true;
        }
    }
    var reg_form = jQuery('.im_register_form');
    reg_form.submit(function()
    {
        if (validate_username() & validate_email())
        {
            return true;
        }
        else
        {
            return false;
        }
    });
});

