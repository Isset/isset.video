<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use Timber\Timber;

class DurationColumn extends BaseAction {
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
		if ( $columnName !== "video-publisher-duration" ) {
			return;
		}

		$postMeta = get_post_meta( $postId, 'video-publish', true );

		if ( !isset($postMeta['metadata']) || $postMeta['metadata'] === null ) {
			return;
		}

		$seconds = $postMeta['metadata'][0]['duration'];

		echo Timber::compile( __DIR__ . "/../../views/admin/duration-column.html.twig", [
			"duration" => sprintf( '%02d:%02d:%02d', ( $seconds / 3600 ), ( $seconds / 60 % 60 ), $seconds % 60 )
		] );
	}

	function getAction() {
		$type = VideoPublisher::getTypeName();

		return "manage_${type}_posts_custom_column";
	}
}