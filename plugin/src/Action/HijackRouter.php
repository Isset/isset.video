<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


class HijackRouter extends BaseAction {
	function execute( $arguments ) {
		if (!isset($_GET['ivp-action'])) {
			return;
		}

		$action = $_GET[ 'ivp-action'];

		if ( $action === null ) {
			return;
		}

		if ($action === 'video-redirect') {
			$uuid =  $_GET['uuid'];
			$video = $this->plugin
				->getVideoPublisherService()
				->getVideoUrl($uuid);

			if (!$video) {
				wp_die('No video found with given uuid');
			}

			wp_redirect($video, 302);
			exit(0);
		}
	}

	function getAction() {
		return 'pre_get_posts';
	}

	public function getPriority() {
		return 1;
	}
}