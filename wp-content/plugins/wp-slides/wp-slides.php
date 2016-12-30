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
Including Files
----------------------------------------------*/
require_once(PLUGIN_DIR . 'includes/functions.php');    // main plugin functions
include(PLUGIN_DIR . 'includes/core.php');              // main plugin functions
include(PLUGIN_DIR . 'includes/hide-admin.php');        // hide admin panel for guests
include(PLUGIN_DIR . 'includes/auto-login.php');        // check auto login links
include(PLUGIN_DIR . 'includes/settings.php');          // the plugin options page HTML
include(PLUGIN_DIR . 'includes/users.php');             // the plugin new fields in users.php
include(PLUGIN_DIR . 'includes/slide-shortcode.php');   // the slides' shortcode
