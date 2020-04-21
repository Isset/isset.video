<?php


namespace IssetBV\VideoPublisher\Wordpress\MetaBox;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use Timber\Post;
use Timber\Timber;

class FrontPage extends BaseMetaBox {
	function getId() {
		return 'video-publisher-front-page';
	}

	public function isVisible() {
		$post = get_post();

		return get_post_meta( $post->ID, 'video-isset-status', true ) !== 'transcoding';
	}

	function getScreen() {
		return VideoPublisher::getTypeName();
	}

	function getContext() {
		return 'side';
	}

	function getTitle() {
		return __( 'Front page video', 'video-publisher-preview' );
	}

	function render() {
		$context            = Timber::context();
		$context['post']    = new Post();
		$context['checked'] = $this->plugin->getFrontPageId() === get_the_ID();
		Timber::render( __DIR__ . '/../../views/metabox/front-page.html.twig', $context );
	}

}