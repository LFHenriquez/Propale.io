<?php

function clean_redirect_wp_admin($args = array())
{
    if (isset($_REQUEST['page'])) {
        $params = 'page=' . $_REQUEST['page'];
        foreach (array('orderby','order','paged','per_page') as $value)
            if ($_REQUEST[$value])
                $params .= '&' . $value . '=' . $_REQUEST[$value];
        foreach ($args as $key => $value)
            $params .= '&' . $key . '=' . $value;
        wp_redirect(admin_url('admin.php?' . $params));
    } else
        wp_redirect(admin_url('admin.php'));
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
    $path = PLUGIN_DIR . $filepath;
    if(file_exists($path)) {
    	$failed = false;
        require($path);
        extract($vars);
        ob_start();
        $body = ob_get_contents();
        ob_end_clean();
        if (!$failed)
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