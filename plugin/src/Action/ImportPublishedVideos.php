<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


class ImportPublishedVideos extends BaseAction {
	function isAdminOnly() {
		return true;
	}

	function execute( $arguments ) {
		$this->plugin->getVideoPublisherService()->updateUploads();
		$this->plugin->getVideoPublisherService()->getPublishedVideos();
	}

	function getAction() {
		return 'wp_ajax_isset-video-sync';
	}
}