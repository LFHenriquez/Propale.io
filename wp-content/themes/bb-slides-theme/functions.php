<?php

// Defines
define( 'FL_CHILD_THEME_DIR', get_stylesheet_directory() );
define( 'FL_CHILD_THEME_URL', get_stylesheet_directory_uri() );

// Classes
require_once 'classes/class-fl-child-theme.php';

// Actions
add_action( 'fl_head', 'FLChildTheme::stylesheet' );

function show_slides( $query )
{
    $query->set( 'post_type', array('slide'));
	
	$user_id = get_current_user_id();
	if ($user_id != 0) {
		$client = new Client($user_id);
			if ($client->is_client()) {
			$slides_groups = $client->get_groups_of_slides();
		    $query->set( 'tax_query', array(
		            array(
		                'taxonomy' => 'slides-group',
		                'field' => 'slug',
		                'terms' => $slides_groups
		            )
		        )
		    );
		}
	}
    return $query;
}
add_filter( 'pre_get_posts', 'show_slides' );