<?php

add_action('admin_notices', 'display_admin_error');
add_action('admin_notices', 'display_admin_notification');

function clean_redirect_wp_admin($args = array(), $page = 'admin.php')
{
    $params = (isset($_REQUEST['page']) && $page == 'admin.php')? 'page=' . $_REQUEST['page']: '';
    foreach (array('orderby','order','paged','per_page') as $value)
        if (!empty($_REQUEST[$value]))
            $params .= '&' . $value . '=' . $_REQUEST[$value];
    foreach ($args as $key => $value)
        if (!empty($value))
            $params .= '&' . $key . '=' . $value;

    if (!empty($params)) {
        wp_redirect(admin_url($page.'?'.$params));
    } else
        wp_redirect(admin_url($page));
}

/**
 * (string) $message - message to be passed to Slack
 * (string) $room - room in which to write the message, too
 * (string) $icon - You can set up custom emoji icons to use with each message
 */
function slack_message($message, $room = null, $icon = ':longbox:')
{
    $webhook = get_option('slack_webhook', null);
    if (!$room)
    	$room = get_option('slack_room', null);
    $data = 'payload=' . json_encode(array(
        'channel' => $room,
        'text' => urlencode($message),
        'icon_emoji' => $icon
    ));
    
    // You can get your webhook endpoint from your Slack settings
    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

function get_email_template($filepath, $vars) {
    // get the template from email folder
    $path = PLUGIN_DIR . "email/" .$filepath;
    if(file_exists($path)) {
        extract($vars);
        ob_start();
        require($path);
        $body = ob_get_contents();
        ob_end_clean();
        return $body;
    }
    return false;
}

function create_autologin_link($user_id)
{
	$user = get_userdata($user_id);
	$login = $user->user_login;
	if ($login)
		return sprintf('%1$s/?login=%2$s&key=%3$s', get_site_url(), $login, md5($login . wp_salt()));
	else
		return get_site_url();
}

function check_autologin_link($login, $key)
{
	return (md5($login . wp_salt()) == $key);
}

function display_admin_error()
{
    global $admin_error;
    if (isset($_REQUEST['admin_error']))
        $admin_error = urldecode($_REQUEST['admin_error']);
    if (isset($admin_error))
        echo '<br><div id="message" class="notice notice-error is-dismissible">' . $admin_error . '</div>';
}

function display_admin_notification()
{
    global $admin_notification;
    if (isset($_REQUEST['admin_notification']))
        $admin_notification = urldecode($_REQUEST['admin_notification']);
    if (isset($admin_notification))
        echo '<br><div id="message" class="notice notice-success is-dismissible">' . $admin_notification . '</div>';
}

function time_ago( $date )
{
    if( empty( $date ) )
    {
        return "";
    }

    $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");

    $lengths = array("60","60","24","7","4.35","12","10");

    $now = current_time( 'timestamp' );

    $unix_date = strtotime( $date );

    // check validity of date

    if( empty( $unix_date ) )
    {
        return "Bad date";
    }

    // is it future date or past date

    if( $now > $unix_date )
    {
        $difference = $now - $unix_date;
        $tense = "ago";
    }
    else
    {
        $difference = $unix_date - $now;
        $tense = "from now";
    }

    for( $j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++ )
    {
        $difference /= $lengths[$j];
    }

    $difference = round( $difference );

    if( $difference != 1 )
    {
        $periods[$j].= "s";
    }

    return "$difference $periods[$j] {$tense}";

}