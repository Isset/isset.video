<?php


namespace IssetBV\VideoPublisher\Wordpress\MetaBox;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use Timber\Post;
use Timber\Timber;

class ThumbnailSelect extends BaseMetaBox {
	function getId() {
		return 'video-publisher-thumbnail-select';
	}

	public function isVisible() {
		$post = get_post();

		return get_post_meta( $post->ID, 'video-isset-status', true ) !== 'transcoding';
	}

	function getTitle() {
		return __( 'Use generated thumbnail', 'isset-video-publisher' );
	}

	function getScreen() {
		return VideoPublisher::getTypeName();
	}

	public function getContext() {
		return 'normal';
	}

	function render() {
		$context         = Timber::context();
		$context['post'] = new Post();

		$item              = get_post_meta( get_the_ID(), 'video-publish', true );
		$context['assets'] = $item['assets'];
		$context['nonce']  = wp_create_nonce( 'isset-video-set-image' );

		Timber::render( __DIR__ . '/../../views/metabox/thumbnail-select.html.twig', $context );
	}

}