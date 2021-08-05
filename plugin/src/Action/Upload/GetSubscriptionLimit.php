<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Upload;

use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;

class GetSubscriptionLimit extends BaseAction {

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
				$this->plugin->getVideoPublisherService()->fetchSubscriptionLimit(),
			)
		);
		exit;
	}

	function getAction() {
		return 'wp_ajax_isset-video-fetch-subscription-limits';
	}
}
