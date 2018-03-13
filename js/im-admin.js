jQuery(document).ready(function() {
    jQuery('.payment').hide();
    jQuery('.mail_listen').hide();
    jQuery('.join_button').hide();
    jQuery('.tip_recurring').hide();
    //Set fields at onetime 
    jQuery('#one_time').change(
            function() {
                if (jQuery(this).is(':checked')) {
                    jQuery('.p_period').hide();
                    jQuery('.no_of_payment').hide();
                    jQuery('.trial_select').hide();
                    jQuery('.trial_price').hide();
                    jQuery('.trial_period').hide();
                    jQuery('.payment_putton').show();
                    jQuery('.join_button').hide();
                    jQuery('.currency').show();
                    jQuery('.product_price').show();
                    jQuery('.subs_period').show();
                    //Frist price change to be product price when onetime is checked
                    jQuery('.product_price_label').html('Product Price:');
                    //Recurring tool tip description will be  hide when onetime is checked and tooltip onetime will be show
                    jQuery('.tip_onetime').show();
                    jQuery('.tip_recurring').hide();
                }
            });
    //Set fields at recurring time
    jQuery('#recurring').change(
            function() {
                if (jQuery(this).is(':checked')) {
                    jQuery('.p_period').show();
                    jQuery('.no_of_payment').show();
                    jQuery('.trial_select').show();
                    jQuery('.trial_price').hide();
                    jQuery('.trial_period').hide();
                    jQuery('.payment_putton').show();
                    jQuery('.join_button').hide();
                    //                if(jQuery('#trial_select').attr('checked')){
                    jQuery('.trial_price').show();
                    jQuery('.trial_period').show();
                    //                }
                    jQuery('.subs_period').hide();
                    //Product price change to be first price when recurring is checked
                    jQuery('.product_price_label').html(object_name.first_price);
                    //Onetime tool tip description will be  hide when recurring is checked and tooltip recurring will be show
                    jQuery('.tip_onetime').hide();
                    jQuery('.tip_recurring').show();
                }
            });
    //Set fields at free member time
    jQuery('#free_mem').change(
            function() {
                if (jQuery(this).is(':checked')) {
                    jQuery('.currency').hide();
                    jQuery('.product_price').hide();
                    jQuery('.p_period').hide();
                    jQuery('.no_of_payment').hide();
                    jQuery('.trial_select').hide();
                    jQuery('.trial_price').hide();
                    jQuery('.trial_period').hide();
                    jQuery('.join_button').show();
                    jQuery('.payment_putton').hide();
                    jQuery('.subs_period').show();
                }
            });
    //Set fields for trial select
    jQuery('.trial_yes').change(
            function() {
                if (jQuery(this).is(':checked')) {
                    jQuery('.trial_price').show();
                    jQuery('.trial_period').show();
                }
            });
    jQuery('.trial_no').change(
            function() {
                if (jQuery(this).is(':checked')) {
                    jQuery('.trial_price').hide();
                    jQuery('.trial_period').hide();
                }
            });
    //Set fields for mali listentioner 
    jQuery("#mailling_list_type").change(function() {
        var option = jQuery("#mailling_list_type").val();
        if (option.toLowerCase() == "aw") {
            jQuery('.mail_listen').show();
        } else {
            jQuery('.mail_listen').hide();
        }
    });
    //Subscription cycle change event
    jQuery('#subs_period_cycle').change(function() {
        var subs_cycle = jQuery("#subs_period_cycle option:selected").val();
        if (subs_cycle == 'U') {
            jQuery('#subs_period').hide();
            jQuery("#subs_period").removeClass('wrong');
            jQuery('#subs_period').attr("placeholder", "");
            jQuery("#subs_period").css("border", "1px solid #c3c3c3");
        } else {
            jQuery('#subs_period').show();
        }
    });
    var subs_cycle = jQuery("#subs_period_cycle option:selected").val();
    if (subs_cycle == 'U') {
        jQuery('#subs_period').hide();
    } else {
        jQuery('#subs_period').show();
    }
});
//tipsy
jQuery(function() {
    jQuery('.tooltip').tipsy({
        gravity: 'n'
    });
});
//Form validation
jQuery(document).ready(function()
{
    //Product Name validator
    var productName = jQuery("#product_name");
    function validate_product_name() {
        if (productName.val() == '') {
            productName.addClass('wrong');
            productName.css('border', 'solid 1px red');
            productName.attr("placeholder", "Required");
            return false;
        } else {
            productName.removeClass('error');
            productName.css("border", "1px solid #c3c3c3");
            return true;
        }
    }
    productName.blur(validate_product_name);
    productName.keyup(validate_product_name);
    //Price Validator
    var product_price = jQuery("#product_price");

    function validate_price()
    {
        if (product_price.val() == '') {
            product_price.addClass('wrong');
            product_price.css('border', 'solid 1px red');
            product_price.attr("placeholder", "Required");
            return false;
        } else {
            product_price.removeClass('error');
            product_price.css("border", "1px solid #c3c3c3");
            return true;
        }
    }
    product_price.blur(validate_price);
    product_price.keyup(validate_price);
    //Payment period validator 
    var payment_period = jQuery("#payment_period");
    function validate_pay_period()
    {
        if (payment_period.val() == '') {
            payment_period.addClass('wrong');
            payment_period.css('border', 'solid 1px red');
            payment_period.attr("placeholder", "Required");
            return false;
        } else {
            payment_period.removeClass('wrong');
            payment_period.css("border", "1px solid #c3c3c3");
            return true;
        }
    }
    //Subscription period validator
    var subs_period = jQuery("#subs_period");
    function validate_subs_period()
    {
        var subs_cycle = jQuery("#subs_period_cycle option:selected").val();
        if (subs_cycle == 'U') {
            jQuery('#subs_period').hide();
            jQuery("#subs_period").removeClass('wrong');
            jQuery('#subs_period').attr("placeholder", "");
            jQuery("#subs_period").css("border", "1px solid #c3c3c3");
        }

        if (subs_period.val() == '') {
            subs_period.addClass('wrong');
            subs_period.css('border', 'solid 1px red');
            subs_period.attr("placeholder", "Required");
            if (jQuery('#recurring').attr('checked')) {
                return true;
            }
            if (subs_cycle == 'U') {
                return true;
            } else {
                return false;
            }
        } else {
            subs_period.removeClass('wrong');
            subs_period.css("border", "1px solid #c3c3c3");
            return true;
        }
    }
    subs_period.blur(validate_subs_period);
    subs_period.keyup(validate_subs_period);
    //Second Price validator
    var second_price = jQuery("#trial_price");
    function validate_second_price()
    {
        if (second_price.val() == '') {
            second_price.addClass('wrong');
            second_price.css('border', 'solid 1px red');
            second_price.attr("placeholder", "Required");
            return false;
        } else {
            second_price.removeClass('wrong');
            second_price.css("border", "1px solid #c3c3c3");
            return true;
        }
    }
    //Second period validator
    var second_period = jQuery("#trial_period");
    function validate_second_period()
    {
        if (second_period.val() == '') {
            second_period.addClass('wrong');
            second_period.css('border', 'solid 1px red');
            second_period.attr("placeholder", "Required");
            return false;
        } else {
            second_period.removeClass('wrong');
            second_period.css("border", "1px solid #c3c3c3");
            return true;
        }
    }
    //Number of payments validator
    var no_of_payment = jQuery("#no_of_payment");
    function validate_no_of_payment()
    {
        if (no_of_payment.val() == '') {
            no_of_payment.addClass('wrong');
            no_of_payment.css('border', 'solid 1px red');
            no_of_payment.attr("placeholder", "Required");
            return false;
        } else {
            no_of_payment.removeClass('wrong');
            no_of_payment.css("border", "1px solid #c3c3c3");
            return true;
        }
    }

    jQuery('#recurring').change(
            function() {
                if (jQuery(this).is(':checked')) {
                    payment_period.blur(validate_pay_period);
                    payment_period.keyup(validate_pay_period);
                    //Second Price validator
                    second_price.blur(validate_second_price);
                    second_price.keyup(validate_second_price);
                    //Second period validator
                    second_period.blur(validate_second_period);
                    second_period.keyup(validate_second_period);
                    //Number of payments validator
                    no_of_payment.blur(validate_no_of_payment);
                    no_of_payment.keyup(validate_no_of_payment);
                    var product_form = jQuery('#product_form');
                    product_form.submit(function()
                    {
                        if (validate_pay_period() & validate_second_price() & validate_second_period() & validate_no_of_payment())
                        {
                            return true;
                        }
                        else
                        {
                            return false;
                        }
                    });
                }
            });


    var product_form = jQuery('#product_form');
    product_form.submit(function()
    {
        if (validate_product_name() & validate_price() & validate_subs_period())
        {
            return true;
        }
        else
        {
            return false;
        }
    });



});

