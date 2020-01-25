<?php

// Register Custom Post Type
function register_drop_post_type() {

	$labels = array(
		'name'                  => _x( 'DropHTML', 'Post Type General Name', 'drop' ),
		'singular_name'         => _x( 'Drop', 'Post Type Singular Name', 'drop' ),
		'menu_name'             => __( 'DropHTML', 'drop' ),
		'name_admin_bar'        => __( 'Drop', 'drop' ),
		'archives'              => __( 'Item Archives', 'drop' ),
		'attributes'            => __( 'Item Attributes', 'drop' ),
		'parent_item_colon'     => __( 'Parent Item:', 'drop' ),
		'all_items'             => __( 'List', 'drop' ),
		'add_new_item'          => __( 'Upload Item', 'drop' ),
		'add_new'               => __( 'Upload', 'drop' ),
		'new_item'              => __( 'New Item', 'drop' ),
		'edit_item'             => __( 'Edit Item', 'drop' ),
		'update_item'           => __( 'Update Item', 'drop' ),
		'view_item'             => __( 'View Item', 'drop' ),
		'view_items'            => __( 'View Items', 'drop' ),
		'search_items'          => __( 'Search Item', 'drop' ),
		'not_found'             => __( 'Not found', 'drop' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'drop' ),
		'featured_image'        => __( 'Featured Image', 'drop' ),
		'set_featured_image'    => __( 'Set featured image', 'drop' ),
		'remove_featured_image' => __( 'Remove featured image', 'drop' ),
		'use_featured_image'    => __( 'Use as featured image', 'drop' ),
		'insert_into_item'      => __( 'Insert into item', 'drop' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'drop' ),
		'items_list'            => __( 'Items list', 'drop' ),
		'items_list_navigation' => __( 'Items list navigation', 'drop' ),
		'filter_items_list'     => __( 'Filter items list', 'drop' ),
    );
    
	$args = array(
		'label'                 => __( 'Drop', 'drop' ),
		'description'           => __( 'Drop Html', 'drop' ),
		'labels'                => $labels,
		'supports'              => array( 'title' ),
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'drop', $args );

}
add_action( 'init', 'register_drop_post_type', 0 );