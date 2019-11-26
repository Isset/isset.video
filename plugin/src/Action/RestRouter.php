<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


use IssetBV\VideoPublisher\Wordpress\Controller\PublisherController;

class RestRouter extends BaseAction {
	function getRoutes() {
		$publisherController = new PublisherController( $this->plugin );

		return [
			'/update-publisher' => $publisherController->get( [ 'edit_posts' ] )->updatePublisher(),
			'/set-image'        => $publisherController->post( [ 'edit_posts' ] )->setImage()
		];
	}

	public function execute( $arguments ) {
		foreach ( $this->getRoutes() as $route => $action ) {
			register_rest_route( 'video-publisher/v1', $route, $action );
		}
	}

	function getAction() {
		return 'rest_api_init';
	}
}