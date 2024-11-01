<?php
/*
Plugin Name: WP Viewport
Description: A handy little tool that display the viewport size of any computer or mobile device for testing responsive web designs and media queries.
Version: 1.0
Author: William G. Rivera
Author URI: http://williamgrivera.com
License: GPLv2
*/

/* ----------------------------------------
* Plugin Globals
----------------------------------------- */

define("WPVIEWPORT_URL", plugins_url()."/wp-viewport");

/* ----------------------------------------
* Install/Uninstall Plugin
----------------------------------------- */

register_activation_hook(__FILE__, 'wpviewport_add_defaults');
register_deactivation_hook( __FILE__, 'wpviewport_uninstall' );

// Define default option settings
function wpviewport_add_defaults() {
    $wpviewport_options = get_option('wpviewport_options');
    if(($wpviewport_options['chk_default_options_db']=='1')||(!is_array($wpviewport_options))) {
        delete_option('wpviewport_options'); // so we don't have to reset all the 'off' checkboxes too! (don't think this is needed but leave for now)
        $arr = array(   "chk_relative_mq" => "",
        				"base_font_size" => "16",
                        "chk_default_options_db" => ""
        );
        update_option('wpviewport_options', $arr);
    }
}

// Delete
function wpviewport_uninstall() {
    wp_deregister_script( 'viewport' );
    wp_deregister_style( 'viewport' );
}

add_action('wp_enqueue_scripts', 'wpviewport_enqueue' );

function wpviewport_enqueue() {
	$options = get_option('wpviewport_options');
    wp_enqueue_style( 'viewport', WPVIEWPORT_URL . '/css/viewport.css');
    wp_enqueue_script( 'viewport', WPVIEWPORT_URL . '/js/viewport.js', array( 'jquery' ), null, true );
    wp_localize_script('viewport', 'wpviewport_vars', array( 
        'iconurl' => WPVIEWPORT_URL . '/images/resize.png',
        'relative_mq' => $options['chk_relative_mq'],
        'fontsize' => $options['base_font_size']
    ));
}

/* ----------------------------------------
* register the plugin settings
----------------------------------------- */

function wpviewport_register_settings() {

    // create whitelist of options
    register_setting( 'wpviewport_plugin_options', 'wpviewport_options', 'wpviewport_validate_options' );
}
//call register settings function
add_action( 'admin_init', 'wpviewport_register_settings');

/* ----------------------------------------
* add subpage in appearance menu
----------------------------------------- */

function wpviewport_settings_menu() {
    add_submenu_page( 
          'options-general.php', 
          'WP Viewport Settings',
          'WP Viewport',
          'manage_options',
          'wpviewport-settings',
          'wpviewport_settings_page'
    );
}

add_action('admin_menu', 'wpviewport_settings_menu', 100);

/* ----------------------------------------
* create the settings page layout
----------------------------------------- */

function wpviewport_settings_page() {  
        
    ?>
    <div class="wrap">
        
        <!-- Display Plugin Icon, Header, and Description -->
        <div class="icon32" id="icon-options-general"><br></div>
        <h2>WP Viewport Settings</h2>

        <!-- Beginning of the Plugin Options Form -->
        <form method="post" action="options.php">
            <?php settings_fields('wpviewport_plugin_options'); ?>
            <?php $options = get_option('wpviewport_options'); ?>

            <!-- Table Structure Containing Form Controls -->
            <!-- Each Plugin Option Defined on a New Table Row -->
            <table class="form-table">

                <tr valign="top">
                    <th scope="row">Relative Viewport Size</th>
                    <td>
                        <input name="wpviewport_options[chk_relative_mq]" type="checkbox" value="1" <?php if (isset($options['chk_relative_mq'])) { checked('1', $options['chk_relative_mq']); } ?> />
                        <br /><span style="color:#666666;margin-left:2px;">Check this if you want to use ems for viewport size</span>
                    </td>
                </tr>

                <!-- Font Size -->
                <tr>
                    <th scope="row">Base Font Size</th>
                    <td>
                        <input type="text" size="5" name="wpviewport_options[base_font_size]" value="<?php echo $options['base_font_size']; ?>" />
                        <br /><span style="color:#666666;margin-left:2px;">The ems in the media queries are based on the browsers font size.</span>
                        <br /><span style="color:#666666;margin-left:2px;">Even if you set your font-size to 12px at the html element, the media query will use your browsers font size. Most browsers default font size is 16px.</span>
                    </td>
                </tr>

                <tr><td colspan="2"><div style="margin-top:10px;"></div></td></tr>
                <tr valign="top" style="border-top:#dddddd 1px solid;">
                    <th scope="row">Database Options</th>
                    <td>
                        <label><input name="wpviewport_options[chk_default_options_db]" type="checkbox" value="1" <?php if (isset($options['chk_default_options_db'])) { checked('1', $options['chk_default_options_db']); } ?> /> Restore defaults upon plugin deactivation/reactivation</label>
                        <br /><span style="color:#666666;margin-left:2px;">Only check this if you want to reset plugin settings upon Plugin reactivation</span>
                    </td>
                </tr>
            </table>
            <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
            </p>
        </form>

    </div>
    <?php   
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wpviewport_validate_options($input) {
    $input['base_font_size'] =  wp_filter_nohtml_kses($input['base_font_size']); // Sanitize textbox input (strip html tags, and escape characters)
    return $input;
}

// Display a Settings link on the main Plugins page
function wpviewport_plugin_action_links( $links, $file ) {

    if ( $file == plugin_basename( __FILE__ ) ) {
        $wpviewport_links = '<a href="'.get_admin_url().'options-general.php?page=wpviewport-settings">'.__('Settings').'</a>';
        // make the 'Settings' link appear first
        array_unshift( $links, $wpviewport_links );
    }
    return $links;
}

add_filter( 'plugin_action_links', 'wpviewport_plugin_action_links', 10, 2 );