<?php

namespace IssetBV\VideoPublisher\Wordpress\Rest;

use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use WP_Query;

class PublishesEndpoint extends BaseEndpoint {
	const LAZY_STEPS = 6;

	function getRoute() {
		return '/publishes';
	}

	public function getMethod() {
		return 'POST';
	}

	function execute( $request ) {
		$reqBody = json_decode( $request->get_body(), true );
		$page    = $reqBody['page'];

		$args = [
			'post_type'      => VideoPublisher::getTypeName(),
			'post_status'    => 'any',
			's'              => $reqBody['post_title'],
			'posts_per_page' => self::LAZY_STEPS,
			'paged'          => intval($page) + 1
		];

		$query = new WP_Query( $args );

		if ( $query->post_count > 0 ) {
			return array_map( function ( $post ) {
				$meta = get_post_meta( $post->ID, 'video-publish', true );

				return [
					'post_name'      => $post->post_name,
					'post_title'     => $post->post_title,
					'post_thumbnail' => get_the_post_thumbnail( $post ),
					'post_size'      => $meta['size']
				];
			}, $query->posts );
		}

		return [];
	}
}