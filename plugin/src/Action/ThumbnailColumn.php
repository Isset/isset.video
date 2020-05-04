<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use Timber\Timber;

class ThumbnailColumn extends BaseAction {
	public function isAdminOnly() {
		return true;
	}


	function getArgs() {
		return 2;
	}

	/**
	 * @inheritDoc
	 */
	function execute( $arguments ) {
		list( $columnName, $postId ) = $arguments;

		if ( $columnName !== "video-publisher-thumbnail" ) {
			return;
		}

		$image  = wp_get_attachment_image_src( get_post_thumbnail_id( $postId ), [ 60, 60 ] );
		$poster = null;

		$postMeta = get_post_meta( $postId, 'video-publish', true );

		if ( isset( $postMeta['metadata'] ) && $postMeta['metadata'] !== null ) {
			$seconds = $postMeta['metadata'][0]['duration'];
			$duration = sprintf( '%02d:%02d:%02d', ( $seconds / 3600 ), ( $seconds / 60 % 60 ), $seconds % 60 );
		} else {
			$duration = '';
		}

		if ( is_array( $image ) ) {
			list( $poster, $w, $h ) = $image;

			echo Timber::compile( __DIR__ . "/../../views/admin/thumbnail-column.html.twig", [
				"poster"   => $poster,
				"duration" => $duration
			] );
		}
	}

	function getAction() {
		$type = VideoPublisher::getTypeName();

		return "manage_${type}_posts_custom_column";
	}
}