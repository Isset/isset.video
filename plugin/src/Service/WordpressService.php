<?php


namespace IssetBV\VideoPublisher\Wordpress\Service;


use IssetBV\VideoPublisher\Wordpress\Plugin;
use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use WP_Query;

class WordpressService {
	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * WordpressService constructor.
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	function createPostForUpload( $id ) {
		$info = $this->plugin->getVideoPublisherService()->fetchUploadInfo( $id );

		$post_data = [
			'post_name'   => 'upload/' . $id,
			'post_title'  => $info['filename'],
			'post_type'   => VideoPublisher::getTypeName(),
			'post_status' => 'draft'
		];

		$post_id = wp_insert_post( $post_data, true );
		update_post_meta( $post_id, 'video-isset-status', 'transcoding' );
		update_post_meta( $post_id, 'video-isset-transcode', $info );

		return get_post( $post_id );
	}

	function updatePostFromPublish( $publish, $force_thumbnail = false ) {
		if ( $publish['status'] === 'online' && (int) $publish['enabled'] === 1 ) {
			$post_status = 'publish';
		} else {
			$post_status = 'draft';
		}

		$args = [
			'post_type'   => VideoPublisher::getTypeName(),
			'name'        => $publish['uuid'],
			'post_status' => [ 'publish', 'draft' ],
		];

		$WP_Query = new WP_Query( $args );

		$post_data = [
			'post_name'   => $publish['uuid'],
			'post_type'   => VideoPublisher::getTypeName(),
			'post_status' => $post_status,
		];

		if ( $WP_Query->post_count === 0 ) {
			$post_data['post_title'] = $publish['description'];

			$post_id = wp_insert_post( $post_data, true );
			$post    = get_post( $post_id );

			$force_thumbnail = true;
		} else {
			$post = $WP_Query->next_post();

			$post_data['ID'] = $post->ID;
			wp_update_post( $post_data );
		}

		if ( $force_thumbnail ) {
			$this->plugin->getThumbnailService()->setThumbnail( $post->ID, $publish['assets'][0]['url'] );
		}

		update_post_meta( $post->ID, 'video-isset-status', 'online' );
		delete_post_meta( $post->ID, 'video-isset-transcode' );
		update_post_meta( $post->ID, 'video-publish', $publish );
	}

	public function getUploadDrafts() {
		$args = [
			'post_type'   => VideoPublisher::getTypeName(),
			'post_status' => 'draft',
		];

		$drafts = [];

		$WP_Query = new WP_Query( $args );

		while ( $post = $WP_Query->next_post() ) {
			if ( get_post_meta( $post->ID, 'video-isset-status', true ) === 'transcoding' ) {
				$drafts[] = $post->ID;
			}
		}

		return $drafts;
	}
}