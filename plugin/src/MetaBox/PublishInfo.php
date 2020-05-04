<?php


namespace IssetBV\VideoPublisher\Wordpress\MetaBox;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use Timber\Post;
use Timber\Timber;

class PublishInfo extends BaseMetaBox {
	function getId() {
		return 'video-publisher-publish-info';
	}

	public function isVisible() {
		$post = get_post();

		return get_post_meta( $post->ID, 'video-isset-status', true ) !== 'transcoding';
	}

	function getTitle() {
		return __( 'Publish info', 'isset-video-publisher' );
	}

	function getScreen() {
		return VideoPublisher::getTypeName();
	}

	public function getContext() {
		return 'side';
	}

	function render() {
		$context         = Timber::context();
		$context['post'] = new Post();

		$item = get_post_meta( get_the_ID(), 'video-publish', true );
		$context['size'] = $item['size'];
		$context['meta'] = $item['metadata'];

		if ($item['metadata']) {
			$seconds = $item['metadata'][0]['duration'];
			$context['duration'] = sprintf('%02d:%02d:%02d', ($seconds/ 3600),($seconds/ 60 % 60), $seconds% 60);
		}

		Timber::render( __DIR__ . '/../../views/metabox/publish-info.html.twig', $context );
	}

}