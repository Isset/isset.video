<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Upload;


use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;
use IssetBV\VideoPublisher\Wordpress\Service\TextService;

class RegisterUpload extends BaseAction {
	public function isAdminOnly() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	function execute( $arguments ) {
		check_ajax_referer( 'isset-video' );
		header( "Content-Type", "application/json" );

		$post_id = TextService::validateAndSanitizeText( $_POST, 'id' );

		if ( $post_id === false ) {
			return;
		}

		$post = $this->plugin->getWordpressService()->createPostForUpload( $post_id );

		echo json_encode( [
			"url" => add_query_arg( [ "post" => $post->ID, 'action' => 'edit' ], 'post.php' ),
		] );

		exit;
	}

	function getAction() {
		return 'wp_ajax_isset-video-register-upload';
	}
}