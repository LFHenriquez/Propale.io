<?php

/*----------------------------------------------
Registering
----------------------------------------------*/
register_activation_hook(__FILE__, 'user_tags_activate');

/*----------------------------------------------
Functions
----------------------------------------------*/

/**
 * Init on activation of plugin
 */
function user_tags_activate()
{
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
}