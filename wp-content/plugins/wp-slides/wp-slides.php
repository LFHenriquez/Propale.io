<?php
/*
Plugin Name: WP Slides
Plugin URI: http://propale.io
Description: Créer des documents personnalisés pour chacun de ses clients
Version: 1.2
Author: Leonard Henriquez
Author URI: https://www.linkedin.com/in/l%C3%A9onard-henriquez-38345a107
License: GPL2
*/

if ( !defined( 'ABSPATH' ) ) {
    die();
}

define('PLUGIN_DIR', plugin_dir_path(__FILE__)); // Defining plugin dir path.
define('PLUGIN_URL', plugin_dir_url( __FILE__ )); // Defining plugin url.

/*----------------------------------------------
Registering
----------------------------------------------*/
register_activation_hook(__FILE__, 'wp_slides_activate');

/*----------------------------------------------
Including Files
----------------------------------------------*/
require_once(PLUGIN_DIR . 'includes/functions.php');    // main plugin functions
include(PLUGIN_DIR . 'includes/core.php');              // main plugin functions
include(PLUGIN_DIR . 'includes/hide-admin.php');        // hide admin panel for guests
include(PLUGIN_DIR . 'includes/auto-login.php');        // check auto login links
include(PLUGIN_DIR . 'includes/admin.php');             // the plugin options page HTML
include(PLUGIN_DIR . 'includes/settings.php');          // settings page for the extension
include(PLUGIN_DIR . 'includes/slide-shortcode.php');   // the slides' shortcode
include(PLUGIN_DIR . 'includes/ajax.php');              // ajax backend
include(PLUGIN_DIR . 'includes/mail-tracking.php');     // mail tracking
// include(PLUGIN_DIR . 'includes/users.php');             // the plugin new fields in users.php

/*----------------------------------------------
Actions
----------------------------------------------*/
add_action( 'admin_init', 'wp_slides_has_parent_plugin' );
add_action( 'init', 'wp_slides_rewrite_rule' );

/*----------------------------------------------
Filters
----------------------------------------------*/
add_filter('query_vars', 'slides_plugin_query_vars');

/*----------------------------------------------
Functions
----------------------------------------------*/

/**
 * Init on activation of plugin
 */
function wp_slides_activate()
{
    global $wpdb;
    global $wp_rewrite;
    
    // Initialize on first activation
    $guest_role = 'guest';
    if (!get_role($guest_role)) {
        add_role($guest_role, 'Guest', array(
            'read' => true,
            'level_0' => true
        ));
        update_option('user_guest_role', $guest_role, yes);
        update_option('default_role', $guest_role, yes);
    }

    if (!get_role('commercial')) {
        add_role('commercial', 'Commercial', array(
            'read' => true,
            'edit_slides' => true,
            'level_0' => true
        ));
    }

    update_option('enable_slack_notification', 'false', yes);
    update_option('slack_room', '', yes);
    update_option('slack_webhook', '', yes);

    $prefix = $wpdb->get_blog_prefix();
    $table_name = $prefix. 'proposal';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
      client_id mediumint(9) NOT NULL,
      user_phone varchar(14),
      user_logo varchar(100),
      groups_of_slides text,
      mail_sent datetime,
      mail_opened datetime,
      last_login datetime,
      PRIMARY KEY  (client_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_rewrite_rule(
        '^mail/img/([0-9]+).png/?',
        'index.php?mail-tracking=1&user-id=$matches[1]',
        'top' );
    $wp_rewrite->flush_rules();
}

function wp_slides_rewrite_rule() {
    add_rewrite_rule(
        '^mail/img/([0-9]+).png/?',
        'index.php?mail-tracking=1&user-id=$matches[1]',
        'top' );    
}

function slides_plugin_query_vars($vars) {
    $vars[] = 'user-id';
    $vars[] = 'mail-tracking';
    return $vars;
}

function wp_slides_has_parent_plugin() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'bb-plugin/fl-builder.php' ) ) {
        add_action( 'admin_notices', 'wp_slides_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) ); 

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
    elseif (is_plugin_active( 'bb-plugin/fl-builder.php' )) {
        $posts_type = get_option('_fl_builder_post_types',array());
        if (!in_array('slide', $posts_type))
            $posts_type[] = 'slide';
        update_option('_fl_builder_post_types', $posts_type, 'yes');
    }
}

function wp_slides_notice(){
    ?><div class="error"><p>Sorry, but WP-Slides Plugin requires the Beaver Builder plugin to be installed and active.</p></div><?php
}