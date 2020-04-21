<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;

class SetFeaturedImage extends BaseAction {
	public function isAdminOnly() {
		return true;
	}

	function execute( $arguments ) {
		check_ajax_referer( 'isset-video' );
		$post_id = $_POST['post_id'];
		$url     = $_POST['url'];

		return $this->plugin->getThumbnailService()->setThumbnail( $post_id, $url );
	}

	function getAction() {
		return 'wp_ajax_isset-video-set-image';
	}
}
