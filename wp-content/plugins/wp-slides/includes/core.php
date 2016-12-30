<?php

/*----------------------------------------------
Action
----------------------------------------------*/
add_action( 'init', 'create_slides_type' );
add_action( 'init', 'create_service_taxonomy');

/*----------------------------------------------
Functions
----------------------------------------------*/

/**
 * Create new post type : slides
 */
function create_slides_type() {
    register_post_type( 'slide',
        array(
            'labels' => array(
                'name' => __( 'Slides' ),
                'singular_name' => __( 'Slide' )
            ),
        'public' => true
        )
    );
}

/**
 * Create new taxonomy : service
 */
function create_service_taxonomy() {
    // Add new taxonomy, make it hierarchical (like categories)
    $labels = array(
        'name'              => _x( 'Services', 'taxonomy general name', 'textdomain' ),
        'singular_name'     => _x( 'Service', 'taxonomy singular name', 'textdomain' ),
        'search_items'      => __( 'Search Services', 'textdomain' ),
        'all_items'         => __( 'All Services', 'textdomain' ),
        'parent_item'       => __( 'Parent Service', 'textdomain' ),
        'parent_item_colon' => __( 'Parent Service:', 'textdomain' ),
        'edit_item'         => __( 'Edit Service', 'textdomain' ),
        'update_item'       => __( 'Update Service', 'textdomain' ),
        'add_new_item'      => __( 'Add New Service', 'textdomain' ),
        'new_item_name'     => __( 'New Service Name', 'textdomain' ),
        'menu_name'         => __( 'Services', 'textdomain' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'service' ),
    );

    register_taxonomy( 'service', array( 'slide' ), $args );
}