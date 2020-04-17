<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;

class SavePost extends BaseAction {
	function getAction() {
		return 'save_post';
	}

	function getArgs() {
		return 2;
	}

	function execute( $args ) {
		list( $post_id ) = $args;
		if ( ! isset( $_POST['isset_publisher_class_nonce'] ) || ! wp_verify_nonce( $_POST['isset_publisher_class_nonce'], basename( __FILE__ ) ) ) {
			return $post_id;
		}

		if ( isset( $_POST['isset_video_publisher_home'] ) ) {
			$this->plugin->setFrontPageId( get_the_ID() );
		} else {
			if ( get_the_ID() === $this->plugin->getFrontPageId() ) {
				$this->plugin->setFrontPageId( false );
			}
		}
	}
}