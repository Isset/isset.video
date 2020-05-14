<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use Timber\Timber;

class ResolutionColumn extends BaseAction {
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

		if ( $columnName !== "video-publisher-max-resolution" || get_post($postId)->post_status === 'draft' ) {
			return;
		}

		$poster = null;

		$postMeta    = get_post_meta( $postId, 'video-publish', true );
		$thumbnailId = get_post_thumbnail_id( $postId );
		$metadata    = wp_get_attachment_metadata( $thumbnailId );

		if ( isset( $postMeta['metadata'] ) && $postMeta['metadata'] !== null ) {
			$biggestResolution = end( $postMeta['metadata'] )['resolution'];
		} else {
			$biggestResolution = "{$metadata['width']}x{$metadata['height']}";
		}

		echo Timber::compile(
			__DIR__ . "/../../views/admin/resolution-column.twig",
			[
				"resolution"  => $biggestResolution,
			]
		);
	}

	function getAction() {
		$type = VideoPublisher::getTypeName();

		return "manage_${type}_posts_custom_column";
	}
}