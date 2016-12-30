<?php
/*
Plugin Name: Custom plugin de Propale.io
Plugin URI: http://propale.io
Description: Plugin des modifications propres au site propale.io
Version: 1.0
Author: Leonard Henriquez
Author URI: https://www.linkedin.com/in/l%C3%A9onard-henriquez-38345a107
License: GPL2
*/

function remove_wp_logo( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'wp-logo' );
}
add_action( 'admin_bar_menu', 'remove_wp_logo', 999 );
