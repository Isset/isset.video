<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Settings;

use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;
use IssetBV\VideoPublisher\Wordpress\Plugin;

class Scripts extends BaseAction {
	public function isAdminOnly() {
		return true;
	}

	function getAction() {
		return 'admin_enqueue_scripts';
	}

	function execute( $arguments ) {
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] !== '' ) {
			return;
		}

		$videoPublishService = $this->plugin->getVideoPublisherService();

		wp_enqueue_script( 'isset-video-main', ISSET_VIDEO_PUBLISHER_URL . 'js/main.js', array( 'jquery' ), true );
		wp_enqueue_script( 'isset-video-publisher-charts', ISSET_VIDEO_PUBLISHER_URL . 'js/admin-charts.js', array( 'jquery' ), true );
		wp_localize_script(
			'isset-video-main',
			'IssetVideoPublisherAjax',
			array(
				'nonce'     => wp_create_nonce( 'isset-video' ),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'postId'    => get_the_ID(),
				'adminUrl'  => admin_url(),
				'loggedIn'  => $videoPublishService->isLoggedIn(),
			)
		);

		wp_localize_script( 'isset-video-main', 'issetVideoTranslations', $this->getTranslationLabels() );
		wp_set_script_translations( 'isset-video-main', 'isset-video-publisher', 'isset-video-publisher/languages' );

		if ( ( isset( $_GET['page'] ) && $_GET['page'] === Plugin::MENU_MAIN_SLUG ) || ( isset( $arguments[0] ) && $this->editingOrNewPost( $arguments[0] ) ) ) {
			$videoArchiveService = $this->plugin->getVideoArchiveService();

			wp_localize_script(
				'isset-video-main',
				'IssetVideoArchiveAjax',
				array(
					'archiveUrl'     => $videoArchiveService->getArchiveUrl(),
					'uploaderUrl'    => $videoPublishService->getUploaderURL(),
					'archiveToken'   => $videoArchiveService->getArchiveToken(),
					'publisherUrl'   => $videoPublishService->getPublisherURL(),
					'publisherToken' => $videoPublishService->getPublisherToken(),
					'root'           => $videoArchiveService->getArchiveRoot(),
				)
			);
		}
	}

	private function editingOrNewPost( $page ) {
		return $page === 'post.php' || $page === 'new.php';
	}

	private function getTranslationLabels() {
		global $l10n;
		$translations = array();

		if ( $l10n ) {
			foreach ( $l10n['isset-video-publisher']->entries as $key => $entry ) {
				$translations[ $key ] = $entry->translations;
			}
		}

		return $translations;
	}
}
