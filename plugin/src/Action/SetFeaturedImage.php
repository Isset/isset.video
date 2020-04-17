<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;

class SetFeaturedImage extends BaseAction {
	function execute( $arguments ) {
		check_ajax_referer( 'set-image' );
		$post_id = $_POST['post_id'];
		$url     = $_POST['url'];

		return $this->plugin->getThumbnailService()->setThumbnail( $post_id, $url );
	}

	function getAction() {
		return 'wp_ajax_isset-video-set-image';
	}

}
