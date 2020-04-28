<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


use IssetBV\VideoPublisher\Wordpress\Plugin;
use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use IssetBV\VideoPublisher\Wordpress\Service\VideoPublisherService;

class DeletePublish extends BaseAction {
	private $service;

	public function __construct( Plugin $plugin ) {
		parent::__construct( $plugin );
		$this->service = new VideoPublisherService( $plugin );
	}

	function execute( $arguments ) {
		list( $postId ) = $arguments;

		if ( get_post_type( $postId ) !== VideoPublisher::getTypeName() ) {
			return;
		}

		$postMetaData = get_post_meta( $postId, 'video-publish', true );
		$this->service->deletePublish( $postMetaData['uuid'] );
	}

	function getAction() {
		return 'before_delete_post';
	}

	public function getPriority() {
		return 1;
	}
}