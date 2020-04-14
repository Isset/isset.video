<?php


namespace IssetBV\VideoPublisher\Wordpress\Service;


use IssetBV\VideoPublisher\Wordpress\Action\SetFeaturedImage;
use IssetBV\VideoPublisher\Wordpress\Plugin;
use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use WP_Query;

class ThumbnailService {
	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * VideoPublisherService constructor.
	 *
	 * @param $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function setThumbnail( $post_id, $url ) {
		$query = new \WP_Query( [
			'p'         => $post_id,
			'post_type' => VideoPublisher::getTypeName(),
		] );

		if ( (int) $query->found_posts !== 1 ) {
			wp_send_json_error( 'Can\'t find video with given post id' );

			return false;
		}

		global $post;
		$query->the_post();

		$uuid = $post->post_name;

		$path = parse_url( $url, PHP_URL_PATH );

		if ( $path === '' || $path === false ) {
			$path = uniqid() . '.jpg';
		}

		$upload_dir = wp_upload_dir();

		$file_name = basename( $path );

		$file_path_tail = $uuid . '/' . $file_name;

		$file_path = $upload_dir['path'] . '/' . $file_path_tail;

		mkdir( dirname( $file_path ), 0777, true );

		$res = wp_remote_request( $url, [
			'stream'   => true,
			'filename' => $file_path,
		] );

		if ( $res instanceof \WP_Error ) {
			wp_send_json_error( 'Failed to download image' );

			return false;
		}


		$post_mime_type = wp_check_filetype( basename( $file_path ), null );

		$attachment_id = wp_insert_attachment( [
			'guid'           => $upload_dir['url'] . '/' . $file_path_tail,
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_path ) ),
			'post_mime_type' => $post_mime_type['type'],
			'post_content'   => '',
			'post_status'    => 'inherit'
		], $file_path, $post_id );

		if ( $attachment_id instanceof \WP_Error ) {
			wp_send_json_error( 'Failed to create attachment' );

			return false;
		}

		// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		// Generate the metadata for the attachment, and update the database record.
		wp_generate_attachment_metadata( $attachment_id, $file_path );

		if ( set_post_thumbnail( $post_id, $attachment_id ) === false ) {
			wp_send_json_error( 'Failed set post thumbnail' );

			return false;
		}

		return true;
	}
}