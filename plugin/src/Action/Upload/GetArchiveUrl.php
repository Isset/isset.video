<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Upload;

use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;

class GetArchiveUrl extends BaseAction {
	public function isAdminOnly() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	function execute( $arguments ) {
		check_ajax_referer( 'isset-video' );
		header( 'Content-Type', 'application/json' );
		echo json_encode(
			array(
				'url' => $this->plugin->getVideoPublisherService()->getArchiveURL(),
			)
		);
		exit;
	}

	function getAction() {
		return 'wp_ajax_isset-video-fetch-archive-url';
	}
}
