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
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] !== '' && $_GET['post_type'] !== 'page' ) {
			// @TODO: Test this. This fixes the javascript not being loaded, but I can't imagine this is here for no reason?
			// return;
		}

		$videoPublishService = $this->plugin->getVideoPublisherService();

		wp_enqueue_script( 'isset-video-main', ISSET_VIDEO_PUBLISHER_URL . 'js/main.js', array( 'jquery' ), ISSET_VIDEO_PUBLISHER_VERSION );
		wp_enqueue_script( 'isset-video-publisher-charts', ISSET_VIDEO_PUBLISHER_URL . 'js/admin-charts.js', array( 'jquery' ), ISSET_VIDEO_PUBLISHER_VERSION );
		wp_localize_script(
			'isset-video-main',
			'IssetVideoPublisherAjax',
			array(
				'nonce'      => wp_create_nonce( 'isset-video' ),
				'restNonce'  => wp_create_nonce( 'wp_rest' ),
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'postId'     => get_the_ID(),
				'adminUrl'   => admin_url(),
				'loggedIn'   => $videoPublishService->isLoggedIn(),
				'mercureUrl' => $videoPublishService->getMercureURL(),
				'division'   => $videoPublishService->fetchCurrentDivision(),
				'pluginUrl'  => ISSET_VIDEO_PUBLISHER_URL,
			)
		);

		if ( $this->editingOrNewPost( $arguments ) ) {
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

		wp_localize_script( 'isset-video-main', 'issetVideoTranslations', $this->getTranslationLabels() );
		wp_set_script_translations( 'isset-video-main', 'isset-video', 'isset-video/languages' );
	}

	private function editingOrNewPost( $arguments ) {
		if ( isset( $_GET['page'] ) ) {
			if ( $_GET['page'] === Plugin::MENU_MAIN_SLUG || $_GET['page'] === Plugin::MENU_LIVESTREAM_SLUG || $_GET['page'] === Plugin::MENU_ADVERTISEMENT_SLUG || $_GET['page'] === Plugin::MENU_PLAYER_SETTINGS_SLUG ) {
				return true;
			}
		}

		if ( isset( $arguments[0] ) ) {
			$page = $arguments[0];

			return $page === 'post.php' || $page === 'new.php' || $page === 'post-new.php';
		}

		return false;
	}

	private function getTranslationLabels() {
		global $l10n;
		$translations = array();

		if ( $l10n && isset( $l10n['isset-video'] ) ) {
			foreach ( $l10n['isset-video']->entries as $key => $entry ) {
				$translations[ $key ] = $entry->translations;
			}
		}

		return $translations;
	}
}
