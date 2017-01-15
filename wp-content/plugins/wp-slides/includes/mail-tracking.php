<?php
function mail_tracking_template_redirect_intercept() {
	global $wp_query;
	if( $wp_query->get('mail-tracking') ) {
		$id = $wp_query->get('user-id');
		$client = new Client($id);
		$client->mail_opened();
		$file = PLUGIN_DIR. '/img/img.png';
		$type = 'image/png';
		header('Content-Type:'. $type);
		header('Content-Length: '. filesize($file));
		readfile($file);
		exit;
	}
}
add_action( 'template_redirect', 'mail_tracking_template_redirect_intercept' );

function mail_tracking_add_rewrite_rules() {
	$rule = '^/mail/img/([0-9]+).png$';
	add_rewrite_rule(
		$rule,
		'index.php?mail-tracking=1&user-id=$matches[1]',
		'top' );
}

function mail_tracking_rewrites_init() {
	add_rewrite_tag( '%mail-tracking%', '([0-9]+)' );
	add_rewrite_tag( '%user-id%', '([0-9]+)' );
	mail_tracking_add_rewrite_rules();
}
add_action( 'init', 'mail_tracking_rewrites_init' );