<?php


namespace IssetBV\VideoPublisher\Wordpress\MetaBox;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use Timber\Post;
use Timber\Timber;

class Preview extends BaseMetaBox {
	function getId() {
		return 'video-publisher-preview';
	}

	function getTitle() {
		return __( 'Publisher preview', 'isset-video-publisher' );
	}

	function getScreen() {
		return VideoPublisher::getTypeName();
	}

	function render() {
		$context         = Timber::context();
		$context['post'] = new Post();
		Timber::render( __DIR__ . '/../../views/metabox/0preview.html.twig', $context );
	}
}