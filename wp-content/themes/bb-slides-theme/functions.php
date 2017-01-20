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
   $query->set( 'post_type', array( 'slide' ) );
    return $query;
}
add_filter( 'pre_get_posts', 'show_slides' );