<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Settings;


use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;
use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;

class Scripts extends BaseAction {
	public function isAdminOnly() {
		return true;
	}

	function getAction() {
		return 'admin_enqueue_scripts';
	}

	function execute( $arguments ) {
		if ( $_GET['post_type'] !== VideoPublisher::getTypeName() ) {
			return;
		}

		wp_enqueue_script( 'isset-video-publisher-edit', ISSET_VIDEO_PUBLISHER_URL . '/js/admin-video-edit.js', [ 'jquery' ], true );
		wp_localize_script( 'isset-video-publisher-edit', 'IssetVideoPublisherAjax', [
			'nonce'   => wp_create_nonce( 'isset-video' ),
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'postId'  => get_the_ID(),
		] );
	}
}