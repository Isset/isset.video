<?php


namespace IssetBV\VideoPublisher\Wordpress\Shortcode;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use stdClass;
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
				'loop'     => '',
				'muted'    => '',
			],
			$params
		);

		$uuid    = $attr['uuid'];
		$post_id = $attr['post_id'];
		$poster  = $attr['poster'];

		$query = new WP_Query( [
			                       'post_type'   => VideoPublisher::getTypeName(),
			                       'post_status' => 'published',
			                       'name'        => $uuid,
		                       ] );

		if ( $query->post_count === 0 ) {
			return Timber::compile( __DIR__ . '/../../views/shortcode/publish-invalid.html.twig' );
		}

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
			'fluid'     => true,
			'techOrder' => [ 'chromecast', 'html5' ],
			'html5'     => [
				'nativeTextTracks' => false,
				'hls' => [
					'handleManifestRedirects' => true,
				],
			],
			"plugins"   => [
				"chromecast" => new StdClass(),
				'airPlay'    => new StdClass(),
			],
		];

		return Timber::compile( __DIR__ . '/../../views/shortcode/publish.html.twig', $context );
	}
}