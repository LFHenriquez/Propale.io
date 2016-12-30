<?php

/*----------------------------------------------
Actions
----------------------------------------------*/
add_action('init', 'auto_login');

/*----------------------------------------------
Functions
----------------------------------------------*/
function auto_login()
{
    global $user_login;
    get_currentuserinfo();
    if (isset($_GET['login']) &&
    	isset($_GET['key']) &&
    	$user_login != $_GET['login'] &&
    	check_autologin_link($_GET['login'], $_GET['key'])) {
        //get user's ID
        $user         = get_user_by('login', $_GET['login']);
        $user_id      = $user->ID;
        $user_login   = $user->user_login;
        $display_name = $user->display_name;
        //login
        if ($user) {
            wp_set_current_user($user_id, $user_login);
            wp_set_auth_cookie($user_id);
            do_action('wp_login', $user_login);
            update_user_option($user_id, 'last_autologin', current_time('mysql'));
            if (get_option('enable_slack_notification',false))
            	slack_message($display_name . ' s\'est connecté sur sa proposition commerciale');
        }   
    }
}
