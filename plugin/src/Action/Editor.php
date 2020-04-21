<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use Timber\Post;
use Timber\Timber;

class Editor extends BaseAction {
	public function isAdminOnly() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	function execute( $arguments ) {
		list( $post ) = $arguments;
		if ( $post->post_type !== VideoPublisher::getTypeName() ) {
			return;
		}


		$transcoding = false;
		if ( get_post_meta( $post->ID, 'video-isset-status', true ) === 'transcoding' ) {
			$transcoding = $this->plugin->getVideoPublisherService()->updateUpload( $post->ID ) === 'transcoding';
		}

		if ( $transcoding ) {
			$this->renderTranscodeStatus();

			return;
		}

		$this->renderPreview();
	}

	function getAction() {
		return 'edit_form_after_title';
	}

	private function renderPreview() {
		$context         = Timber::context();
		$context['post'] = new Post();
		Timber::render( __DIR__ . '/../../views/admin/preview.html.twig', $context );
	}

	private function renderTranscodeStatus() {
		$context['post']   = new Post();
		$context['status'] = get_post_meta( $context['post']->id, 'video-isset-transcode', true );
		Timber::render( __DIR__ . '/../../views/admin/transcoding.html.twig', $context );
	}
}