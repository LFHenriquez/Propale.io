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
include(PLUGIN_DIR . 'includes/admin.php');		        // the plugin options page HTML
include(PLUGIN_DIR . 'includes/settings.php');          // settings page for the extension
include(PLUGIN_DIR . 'includes/users.php');             // the plugin new fields in users.php
include(PLUGIN_DIR . 'includes/slide-shortcode.php');   // the slides' shortcode
include(PLUGIN_DIR . 'includes/ajax.php');   			// ajax backend


/*----------------------------------------------
Functions
----------------------------------------------*/

/**
 * Init on activation of plugin
 */
function wp_slides_activate()
{
	global $wpdb;
    // Initialize on first activation
    $guest_role = 'guest';
    if (!get_role($guest_role)) {
        add_role($guest_role, 'Guest', array(
            'read' => true,
            'level_0' => true
        ));
        update_option('user_guest_role', $guest_role, yes);
        update_option('default_role', $guest_role, yes);
        update_option('enable_slack_notification', 'false', yes);
        update_option('slack_room', '', yes);
        update_option('slack_webhook', '', yes);
    }
    flush_rewrite_rules();

    $prefix = $wpdb->get_blog_prefix();
    $table_name	= $prefix. 'proposal';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
	  client_id mediumint(9) NOT NULL AUTO_INCREMENT,
	  groups_of_slides text,
	  mail_sent datetime,
	  mail_opened datetime,
	  last_connection datetime,
	  phone varchar(14),
	  PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}