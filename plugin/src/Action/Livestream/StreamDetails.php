<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Livestream;

use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;

class StreamDetails extends BaseAction {

	public function isAdminOnly() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	function execute( $arguments ) {
		check_ajax_referer( 'isset-video' );
		header( 'Content-Type', 'application/json' );

		if ( array_key_exists( 'uuid', $_POST ) ) {
			echo json_encode(
				$this->plugin->getVideoPublisherService()->getLivestreamDetails( $_POST['uuid'] )
			);
			exit;
		}
	}

	function getAction() {
		return 'wp_ajax_isset-video-fetch-livestream-details';
	}
}
