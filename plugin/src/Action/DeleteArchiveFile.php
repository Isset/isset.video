<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


use IssetBV\VideoPublisher\Wordpress\Plugin;
use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use IssetBV\VideoPublisher\Wordpress\Service\VideoPublisherService;

class DeleteArchiveFile extends BaseAction {
	function execute( $arguments ) {
		list( $postId ) = $arguments;

		$service = $this->plugin->getVideoArchiveService();

		if ( get_post_type( $postId ) !== VideoPublisher::getTypeName() ) {
			return;
		}

		$postMetaData = get_post_meta( $postId, 'video-publish', true );
		$service->deleteArchiveFile( $postMetaData['identifier'] );
	}

	function getAction() {
		return 'before_delete_post';
	}

	public function getPriority() {
		return 1;
	}
}