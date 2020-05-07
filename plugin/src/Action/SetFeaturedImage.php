<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;

use IssetBV\VideoPublisher\Wordpress\Service\TextService;

class SetFeaturedImage extends BaseAction {
	public function isAdminOnly() {
		return true;
	}

	function execute( $arguments ) {
		check_ajax_referer( 'isset-video' );

		$post_id = TextService::validateAndSanitizeText($_POST['post_id']);
		$url = TextService::validateAndSanitizeText($_POST['url']);

		if ($post_id === false || $url === false) {
			return false;
		}

		return $this->plugin->getThumbnailService()->setThumbnail( $post_id, $url );
	}

	function getAction() {
		return 'wp_ajax_isset-video-set-image';
	}
}
