<?php


namespace IssetBV\VideoPublisher\Wordpress\Shortcode;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use Timber\Timber;
use WP_Query;

class Publish extends ShortcodeBase {
	const CODE = "publish";

	function generate( $params, $content = null ) {
		$attr = shortcode_atts(
			[
				'uuid'     => false,
				'post_id'  => false,
				'poster'   => false,
				'controls' => 'controls',
				'autoplay' => '',
				'loop'     => 'loop',
				'muted'    => '',
			],
			$params
		);

		$uuid    = $attr['uuid'];
		$post_id = $attr['post_id'];
		$poster  = $attr['poster'];


		/* @var int $post_id */

		if ( $uuid === false && $post_id === false ) {
			return false;
		}

		$args = [];
		if ( $uuid ) {
			$args['post_name__in'] = [ $uuid ];
		}
		$args['p']         = $post_id;
		$args['post_type'] = VideoPublisher::getTypeName();
		$query             = new WP_Query( $args );
		if ( (int) $query->found_posts !== 1 ) {
			return $content;
		}

		global $post;
		$query->the_post();

		$uuid  = $post->post_name;
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'large' );
		if ( is_array( $image ) ) {
			list( $poster, $w, $h ) = $image;
		}

		wp_reset_query();

		$video_url = $this->plugin
			->getVideoPublisherService()
			->getVideoUrlForWordpress( $uuid );

		$context                = $attr;
		$context['poster']      = $poster;
		$context['uuid']        = $uuid;
		$context['video_url']   = $video_url;
		$context['video_setup'] = [
			'fluid' => true,
			'html5' => [
				'hls' => [
					'handleManifestRedirects' => true,
				],
			],
		];

		add_action( 'wp_head', function () use ($context) {
			echo Timber::render(__DIR__ . '/../../views/opengraph/video-data.html.twig', ['videoUrl' => $context['video_url']]);
		}, 1, 1 );

		return Timber::compile( __DIR__ . '/../../views/shortcode/publish.html.twig', $context );
	}
}