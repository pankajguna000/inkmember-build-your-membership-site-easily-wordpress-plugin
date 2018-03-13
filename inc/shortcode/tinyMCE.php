<?php

class im_add_shortcode {

    var $pluginname = "inkmember_buttons";

    function im_add_shortcode() {

        // init process for button control
        add_action('admin_init', array(&$this, 'shortcode_buttons'));
    }

    /**
     * Registers the buttons for use
     */
    function register_buttons($buttons) {
        /**
         * inserts a separator between existing buttons and our new one
         * "inkbtn_button" is the ID of our button
         */
        array_push($buttons, "|", $this->pluginname);
        return $buttons;
    }

    /**
     * Filters the tinyMCE buttons and adds our custom buttons
     */
    function shortcode_buttons() {
        /**
         *  Don't bother doing this stuff if the current user lacks permissions
         */
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages'))
            return;

        /**
         *  Add only in Rich Editor mode
         */
        if (get_user_option('rich_editing') == 'true') {
            /**
             *  Filter the tinyMCE buttons and add our own
             */
            add_filter("mce_external_plugins", array($this, "add_tinymce_plugin"));
            add_filter('mce_buttons', array($this, 'register_buttons'));
        }
    }

    /**
     * Add the button to the tinyMCE bar
     */
    function add_tinymce_plugin($plugin_array) {
        global $fscb_base_dir;
        $plugin_array[$this->pluginname] = IM_PLUGIN_PATH . 'inc/shortcode/shortcode.js';
        return $plugin_array;
    }

}

// Call it now
$tinymce_button = new im_add_shortcode ();
