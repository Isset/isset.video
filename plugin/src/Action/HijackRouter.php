<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;

use IssetBV\VideoPublisher\Wordpress\Service\TextService;

class HijackRouter extends BaseAction {
	function execute( $arguments ) {
		$action = TextService::validateAndSanitizeText( $_GET, 'ivp-action' );
		if ( $action === false ) {
			return;
		}

		if ( $action === 'auth' ) {
			// Remove archive auth info from session
			$this->plugin->getVideoArchiveService()->removeAuthToken();

			$token = TextService::validateAndSanitizeText( $_GET, 'token' );
			if ( $token === false ) {
				return;
			}

			$this->plugin->getVideoPublisherService()->updateAuthToken( $token );

			wp_redirect( $this->plugin->getOverviewPageUrl(), 302 );
			exit( 0 );
		}

		if ( $action === 'deauth' ) {
			// Remove archive auth info from session
			$this->plugin->getVideoArchiveService()->removeAuthToken();
			$this->plugin->getVideoPublisherService()->logout();

			wp_redirect( $this->plugin->getOverviewPageUrl(), 302 );
			exit( 0 );
		}

		if ( $action === 'video-redirect' ) {
			$uuid = TextService::validateAndSanitizeText( $_GET, 'uuid' );
			if ( $uuid === false ) {
				return;
			}

			$video = $this->plugin->getVideoPublisherService()->getVideoUrl( $uuid );

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
