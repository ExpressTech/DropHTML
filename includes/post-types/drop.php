<?php

// Register Custom Post Type
function register_drop_post_type() {

	$labels = array(
		'name'                  => _x( 'DropHTML', 'Post Type General Name', 'drophtml' ),
		'singular_name'         => _x( 'Drop', 'Post Type Singular Name', 'drophtml' ),
		'menu_name'             => __( 'DropHTML', 'drophtml' ),
		'name_admin_bar'        => __( 'Drop', 'drophtml' ),
		'archives'              => __( 'Item Archives', 'drophtml' ),
		'attributes'            => __( 'Item Attributes', 'drophtml' ),
		'parent_item_colon'     => __( 'Parent Item:', 'drophtml' ),
		'all_items'             => __( 'List', 'drophtml' ),
		'add_new_item'          => __( 'Upload Item', 'drophtml' ),
		'add_new'               => __( 'Upload', 'drophtml' ),
		'new_item'              => __( 'New Item', 'drophtml' ),
		'edit_item'             => __( 'Edit Item', 'drophtml' ),
		'update_item'           => __( 'Update Item', 'drophtml' ),
		'view_item'             => __( 'View Item', 'drophtml' ),
		'view_items'            => __( 'View Items', 'drophtml' ),
		'search_items'          => __( 'Search Item', 'drophtml' ),
		'not_found'             => __( 'Not found', 'drophtml' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'drophtml' ),
		'featured_image'        => __( 'Featured Image', 'drophtml' ),
		'set_featured_image'    => __( 'Set featured image', 'drophtml' ),
		'remove_featured_image' => __( 'Remove featured image', 'drophtml' ),
		'use_featured_image'    => __( 'Use as featured image', 'drophtml' ),
		'insert_into_item'      => __( 'Insert into item', 'drophtml' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'drophtml' ),
		'items_list'            => __( 'Items list', 'drophtml' ),
		'items_list_navigation' => __( 'Items list navigation', 'drophtml' ),
		'filter_items_list'     => __( 'Filter items list', 'drophtml' ),
	);

	$args = array(
		'label'                 => __( 'Drop', 'drophtml' ),
		'description'           => __( 'Drop Html', 'drophtml' ),
		'labels'                => $labels,
		'supports'              => array( 'title' ),
		'hierarchical'          => false,
		'public'                => true,
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

	register_post_type(DROPHTML_POSTTYPE, $args);
}
add_action( 'init', 'register_drop_post_type', 0 );
