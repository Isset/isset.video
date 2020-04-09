<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


class HijackRouter extends BaseAction {
	function execute( $arguments ) {
		if ( ! isset( $_GET['ivp-action'] ) ) {
			return;
		}

		$action = $_GET['ivp-action'];

		if ( $action === null ) {
			return;
		}

		if ( $action === 'auth' ) {
			$token = $_GET['token'];
			$this->plugin
				->getVideoPublisherService()
				->updateAuthToken( $token );

			wp_redirect( admin_url( "options-general.php?page=isset-video-publisher-admin" ), 302 );
			exit( 0 );
		}

		if ( $action === 'deauth' ) {
			$this->plugin
				->getVideoPublisherService()
				->logout();

			wp_redirect( admin_url( "options-general.php?page=isset-video-publisher-admin" ), 302 );
			exit( 0 );
		}

		if ( $action === 'video-redirect' ) {
			$uuid  = $_GET['uuid'];
			$video = $this->plugin
				->getVideoPublisherService()
				->getVideoUrl( $uuid );

			if ( ! $video ) {
				wp_die( 'No video found with given uuid' );
			}

			wp_redirect( $video, 302 );
			exit( 0 );
		}
	}

	function getAction() {
		return 'pre_get_posts';
	}

	public function getPriority() {
		return 1;
	}
}