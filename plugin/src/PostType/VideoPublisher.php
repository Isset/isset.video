<?php


namespace IssetBV\VideoPublisher\Wordpress\PostType;


class VideoPublisher {
	static public function getTypeName() {
		return 'video-publisher';
	}

	static public function getLabels() {
		return [
			'name'               => _x( 'Videos', 'post type general name', 'isset-video-publisher' ),
			'singular_name'      => _x( 'Video', 'post type singular name', 'isset-video-publisher' ),
			'menu_name'          => _x( 'Videos', 'admin menu', 'isset-video-publisher' ),
			'name_admin_bar'     => _x( 'Video', 'add new on admin bar', 'isset-video-publisher' ),
			'add_new'            => _x( 'Import from publisher', 'video', 'isset-video-publisher' ),
			'add_new_item'       => __( 'Add New Video', 'isset-video-publisher' ),
			'new_item'           => __( 'Import from publisher', 'isset-video-publisher' ),
			'edit_item'          => __( 'Edit Video', 'isset-video-publisher' ),
			'view_item'          => __( 'View Video', 'isset-video-publisher' ),
			'all_items'          => __( 'All Videos', 'isset-video-publisher' ),
			'search_items'       => __( 'Search Videos', 'isset-video-publisher' ),
			'parent_item_colon'  => __( 'Parent Videos:', 'isset-video-publisher' ),
			'not_found'          => __( 'No videos found.', 'isset-video-publisher' ),
			'not_found_in_trash' => __( 'No videos found in Trash.', 'isset-video-publisher' ),
		];
	}

	static public function getArgs() {
		return [
			'labels'             => static::getLabels(),
			'description'        => __( 'Description.', 'isset-video-publisher' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'video-publisher' ],
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 30,
			'menu_icon'          => 'dashicons-video-alt',
			'supports'           => [ 'title', 'thumbnail' ],
		];
	}
}