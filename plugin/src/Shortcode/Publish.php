<?php


namespace IssetBV\VideoPublisher\Wordpress\Shortcode;


use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use IssetBV\VideoPublisher\Wordpress\Renderer;
use Timber\Timber;
use WP_Query;

class Publish extends ShortcodeBase {
	const CODE = "publish";

	function generate( $params, $content = null ) {
		// If we have a video, attach chrome cast framework
		wp_enqueue_script( 'chrome_cast',
			"https://www.gstatic.com/cv/js/sender/v1/cast_sender.js?loadCastFramework=1" );

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
		    return Renderer::render( 'shortcode/publish-invalid.php' );
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

		$context              = $attr;
		$context['poster']    = $poster;
		$context['uuid']      = $uuid;
		$context['video_url'] = $video_url;

		return Renderer::render( 'shortcode/publish.php', $context );
	}
}