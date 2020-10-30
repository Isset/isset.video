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
	    if ( isset($_GET['post_type']) && $_GET['post_type'] !== '' ) {
			return;
		}

		wp_enqueue_script( 'isset-video-publisher-edit', ISSET_VIDEO_PUBLISHER_URL . 'js/admin-video-edit.js', [ 'jquery' ], true );
		wp_enqueue_script( 'isset-video-publisher-charts', ISSET_VIDEO_PUBLISHER_URL . 'js/admin-charts.js', [ 'jquery' ], true );
		wp_localize_script( 'isset-video-publisher-edit', 'IssetVideoPublisherAjax', [
			'nonce'   => wp_create_nonce( 'isset-video' ),
            'restNonce'   => wp_create_nonce( 'wp_rest' ),
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'postId'  => get_the_ID(),
            'adminUrl' => admin_url(),
		] );

		if ( ( isset( $_GET['page'] ) && $_GET['page'] === Plugin::MENU_MAIN_SLUG ) || ( isset( $arguments[0] ) && $this->editingOrNewPost( $arguments[0] ) ) ) {
            $videoArchiveService = $this->plugin->getVideoArchiveService();
            $videoPublishService = $this->plugin->getVideoPublisherService();

            wp_enqueue_script( 'isset-video-archive', ISSET_VIDEO_PUBLISHER_URL . 'js/admin-video-edit.js' );
            wp_localize_script( 'isset-video-archive', 'IssetVideoArchiveAjax', [
                'archiveUrl' => $videoArchiveService->getArchiveUrl(),
                'archiveToken' => $videoArchiveService->getArchiveToken(),
                'publisherUrl' => $videoPublishService->getPublisherURL(),
                'publisherToken' => $videoPublishService->getPublisherToken(),
                'root' => $videoArchiveService->getArchiveRoot(),
            ] );
        }
	}

    private function editingOrNewPost( $page )
    {
        return $page === 'post.php' || $page === 'new.php';
    }
}