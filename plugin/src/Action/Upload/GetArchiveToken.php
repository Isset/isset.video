<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Upload;

use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;

class GetArchiveToken extends BaseAction {
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
				'token' => $this->plugin->getVideoPublisherService()->exchangeToken( 'archive' ),
			)
		);
		exit;
	}

	function getAction() {
		return 'wp_ajax_isset-video-fetch-archive-token';
	}
}
