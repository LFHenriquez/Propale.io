<?php

/*----------------------------------------------
Action
----------------------------------------------*/
add_action( 'init', 'create_slides_type' );
add_action( 'init', 'create_slides_taxonomy');

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
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'has_archive' => true,
        'can_export' => true,
        'rewrite' => true,
        'supports' => array(
            'title' => true,
            'editor' => true,
            'author' => true,
            'thumbnail' => true,
            'excerpt' => false,
            'custom-fields' => true,
            'comments' => false,
            'revisions' => true,
            'page-attributes' => false
        ),
        'capabilities' => array(
            'edit_post' => 'edit_slide',
            'edit_posts' => 'edit_slides',
            'edit_others_posts' => 'edit_other_slides',
            'publish_posts' => 'publish_slides',
            'read_post' => 'read_slide',
            'read_private_posts' => 'read_private_slides',
            'delete_post' => 'delete_slide'
        ),
        'map_meta_cap' => true
    ));
}

/**
 * Create new taxonomy : group of slides
 */
function create_slides_taxonomy() {
    // Add new taxonomy, make it hierarchical (like categories)
    $labels = array(
        'name'              => _x( 'Groups of slides', 'taxonomy general name', 'textdomain' ),
        'singular_name'     => _x( 'Group of slides', 'taxonomy singular name', 'textdomain' ),
        'search_items'      => __( 'Search groups of slides', 'textdomain' ),
        'all_items'         => __( 'All groups of slides', 'textdomain' ),
        'parent_item'       => __( 'Parent group of slides', 'textdomain' ),
        'parent_item_colon' => __( 'Parent group of slides:', 'textdomain' ),
        'edit_item'         => __( 'Edit group of slides', 'textdomain' ),
        'update_item'       => __( 'Update group of slides', 'textdomain' ),
        'add_new_item'      => __( 'Add new group of slides', 'textdomain' ),
        'new_item_name'     => __( 'New name for group of slides', 'textdomain' ),
        'menu_name'         => __( 'Groups of slides', 'textdomain' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'slides-group' ),
    );

    register_taxonomy( 'slides-group', array( 'slide' ), $args );
}