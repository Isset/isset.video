<?php


namespace IssetBV\VideoPublisher\Wordpress\Controller;


use IssetBV\VideoPublisher\Wordpress\Internal\Action;

class PublisherController extends BaseController {
	public function updatePublisher() {
		$this->plugin->getVideoPublisherService()->getPublishedVideos();

		return rest_ensure_response( [
			'message' => 'videos updated'
		] );
	}

	public function setImage( $request ) {
		var_dump( $request );
		$item = get_post_meta( $request['uuid'], 'video-publish', true );
		var_dump( $item['assets'] );
	}
}