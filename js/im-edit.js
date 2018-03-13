jQuery(document).ready(function() {
    if (jQuery('#one_time').attr('checked')) {
        jQuery('.p_period').hide();
        jQuery('.no_of_payment').hide();
        jQuery('.trial_select').hide();
        jQuery('.trial_price').hide();
        jQuery('.trial_period').hide();
        jQuery('.currency').show();
        jQuery('.product_price').show();
    }
    if (jQuery('#recurring').attr('checked')) {
        jQuery('.join_button').hide();
        jQuery('.p_period').show();
        jQuery('.no_of_payment').show();
        jQuery('.trial_select').show();
        //        if(jQuery('#trial_select').attr('checked')){
        jQuery('.trial_price').show();
        jQuery('.trial_period').show();
        //        }
        //Product price change to be first price when recurring is checked
        jQuery('.product_price_label').html('First Price:');
    }
    if (jQuery('#free_mem').attr('checked')) {
        jQuery('.join_button').show();
        jQuery('.payment_putton').hide();
        jQuery('.p_period').hide();
        jQuery('.no_of_payment').hide();
        //        jQuery('.trial_select').hide();
        jQuery('.product_price').hide();
        //        jQuery('.trial_price').hide();
        //        jQuery('.trial_period').hide();
        jQuery('.currency').hide();
    }
    if (jQuery('#mailling_list_type').val() == 'AW') {
        jQuery('.mail_listen').show();
    }
    //Subscription cycle change event
    jQuery('#subs_period_cycle').change(function() {
        var subs_cycle = jQuery("#subs_period_cycle option:selected").val();
        if (subs_cycle == 'U') {
            jQuery('#subs_period').hide();
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
})