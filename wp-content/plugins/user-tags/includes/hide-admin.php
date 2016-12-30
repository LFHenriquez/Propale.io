<?php

/*----------------------------------------------
Actions
----------------------------------------------*/
add_action('init', 'wp_admin_no_show_admin_bar_disable');
add_action('admin_init', 'wp_admin_no_show_admin_redirect', 0);

/*----------------------------------------------
Functions
----------------------------------------------*/
/**
 * Redirect users on any wp-admin pages
 */
function wp_admin_no_show_admin_redirect()
{
    // Whitelist multisite super admin
    if (function_exists('is_multisite')) {
        if (is_multisite() && is_super_admin()) {
            return;
        }
    }
    
    $user = wp_get_current_user();
    $role = get_option('user_guest_role', 'guest');
    if (in_array($role, (array) $user->roles)) {
        //The user has the role {$role}
        $redirect = get_bloginfo('url');
        wp_redirect($redirect);
        exit();
    }
}

/**
 * Disable admin bar for users with selected role
 */
function wp_admin_no_show_admin_bar_disable()
{
    // Whitelist multisite super admin
    if (function_exists('is_multisite')) {
        if (is_multisite() && is_super_admin()) {
            return;
        }
    }
    
    $user = wp_get_current_user();
    $role = get_option('user_guest_role', 'guest');
    if (in_array($role, (array) $user->roles)) {
        add_filter('show_admin_bar', '__return_false');
        remove_action('personal_options', '_admin_bar_preferences');
        remove_action('wp_head', '_admin_bar_bump_cb');
    }
}