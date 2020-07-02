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

		if ( $columnName !== "video-publisher-thumbnail" || get_post( $postId )->post_status === 'draft' ) {
			return;
		}

		$poster = null;

		$postMeta    = get_post_meta( $postId, 'video-publish', true );
		$thumbnailId = get_post_thumbnail_id( $postId );
		$metadata    = wp_get_attachment_metadata( $thumbnailId );

		if ( isset( $postMeta['metadata'] ) && $postMeta['metadata'] !== null ) {
			$resolution = array_map(
				function ( $resolution ) {
					return intval( $resolution );
				},
				explode( 'x', array_values($postMeta['metadata'])[0]['resolution'] )
			);
		} else {
			$resolution = [];
		}

		// If we fail to parse the resolution from the post meta
		// Try to use thumbnail data
		if ( count( $resolution ) !== 2 && $metadata !== false && isset( $metadata['width'] ) && isset( $metadata['height'] ) ) {
			$resolution = [ $metadata['width'], $metadata['height'] ];
		}

		// If we still fail by then
		// Use the most common resolution
		if ( count( $resolution ) !== 2 ) {
			$resolution = [ 16, 9 ];
		}

		$minimumSize = 60;

		list( $width, $height ) = $resolution;
		$ratio = $width / $height;

		if ( $width > $height ) {
			$width  = $ratio * $minimumSize;
			$height = $minimumSize;
		} else {
			$width  = $minimumSize;
			$height = $minimumSize / $ratio;
		}

		$image = wp_get_attachment_image_src( $thumbnailId, [ $width, $height ] );

		if ( is_array( $image ) ) {
			list( $poster, $w, $h ) = $image;

			echo Timber::compile(
				__DIR__ . "/../../views/admin/thumbnail-column.html.twig",
				[
					"poster"  => $poster,
					"assets"  => $postMeta['assets'],
					"preview" => isset( $postMeta['preview'] ) ? $postMeta['preview'] : false
				]
			);
		}
	}

	function getAction() {
		$type = VideoPublisher::getTypeName();

		return "manage_${type}_posts_custom_column";
	}
}