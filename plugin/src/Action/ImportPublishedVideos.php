<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;

class ImportPublishedVideos extends BaseAction {
	function isAdminOnly() {
		return true;
	}

	function execute( $arguments ) {
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] === VideoPublisher::getTypeName() ) {
			$this->plugin->getVideoPublisherService()->getPublishedVideos();
			wp_redirect( 'edit.php?post_type=' . VideoPublisher::getTypeName() );
		}
	}

	function getAction() {
		return 'load-post-new.php';
	}
}