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
	// retirer le menu "wordpress" dans le coin en haut à droite de la barre d'administration
	$wp_admin_bar->remove_node( 'wp-logo' );
	// retirer le menu des commentaires
	$wp_admin_bar->remove_node( 'comments' );
}
add_action( 'admin_bar_menu', 'remove_wp_logo', 999 );

function remove_menus() {
	// if (!current_user_can('update_core')) {
	// retirer les menu du menu de gauche
	// remove_menu_page( 'index.php' );	     //Dashboard
	// remove_menu_page( 'upload.php' );                 //Media
	// remove_menu_page( 'themes.php' );                 //Appearance
	// remove_menu_page( 'plugins.php' );                //Plugins
	// }
	remove_menu_page( 'tools.php' );                  //Tools
	remove_menu_page( 'edit-comments.php' );          //Comments
	remove_menu_page( 'users.php' );                  //Users
	remove_menu_page( 'edit.php' );                   //Posts
	remove_menu_page( 'edit.php?post_type=page' );    //Pages
	remove_submenu_page( 'options-general.php', 'options-writing.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
	remove_submenu_page( 'options-general.php', 'options-permalink.php' );
	remove_submenu_page( 'options-general.php', 'options-media.php' );
	remove_submenu_page( 'themes.php', 'theme-editor.php' );
	remove_submenu_page( 'themes.php', 'widgets.php' );
	remove_submenu_page( 'plugins.php', 'plugin-editor.php' );
}
add_action( 'admin_menu', 'remove_menus', 999 );

function clean_footer() {
    add_filter( 'admin_footer_text',    '__return_false', 11 );
    add_filter( 'update_footer',        '__return_false', 11 );
}
add_action( 'admin_menu', 'clean_footer', 999 );

function hide_core_update_notice() {
    if (!current_user_can('update_core')) {
        remove_action( 'admin_notices', 'update_nag', 3 );
    }
}
add_action( 'admin_head', 'hide_core_update_notice', 999 );


// Print jQuery that removes unneeded elements
function remove_user_fields(){
?>
	<script type="text/javascript">
		jQuery(document).ready( function($) {
			var ids = [
				'#rich_editing', // Rich editing button
				'#color-picker', // Admin color scheme
				'#admin_bar_front', // Admin bar when visiting site
				'#nickname', // Nickname
				'#display_name', // Display name
				'#comment_shortcuts', // Keyboard shortcuts for comment moderation
				'#url', // Website
				'#description'
	        ]; // User bio
	      	for (var i = 0; i < ids.length; i++) {
	        	$(ids[i]).closest('tr').remove();
	      	}
	      	$('h2').remove();
	    });
	</script>
<?php 
}
add_action( 'admin_print_footer_scripts-profile.php', 'remove_user_fields' );

function remove_dashboard_meta() {
        remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_secondary', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');//since 3.8
}
add_action( 'admin_init', 'remove_dashboard_meta' );