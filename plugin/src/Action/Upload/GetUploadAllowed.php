<?php

namespace IssetBV\VideoPublisher\Wordpress\Action\Upload;

use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;

class GetUploadAllowed extends BaseAction {

	public function isAdminOnly() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	function execute( $arguments ) {
		check_ajax_referer( 'isset-video' );
		header( 'Content-Type', 'application/json' );

		echo wp_json_encode(
			array(
				'allowed' => $this->plugin->getVideoPublisherService()->uploadingAllowed(),
			)
		);
		exit;
	}

	function getAction() {
		return 'wp_ajax_isset-video-fetch-uploading-allowed';
	}
}
