<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Upload;


use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;

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

		if ( ! isset( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) ) {
			return;
		}

		$post = $this->plugin->getWordpressService()->createPostForUpload( $_POST['id'] );

		echo json_encode( [
			"url" => add_query_arg( [ "post" => $post->ID, 'action' => 'edit' ], 'post.php' ),
		] );

		exit;
	}

	function getAction() {
		return 'wp_ajax_isset-video-register-upload';
	}
}