<?php

namespace IssetBV\VideoPublisher\Wordpress\Rest;

use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use WP_Query;

class PublishesEndpoint
{
	const LAZY_STEPS = 6;

	public static function publishes()
    {
        add_action('rest_api_init', function () {
            register_rest_route('isset-publisher/v1', '/publishes', [
                'methods' => 'POST',
                'callback' => function ($request) {
                    $reqBody = json_decode($request->get_body(), true);

                    $args = [
                        'post_type' => VideoPublisher::getTypeName(),
                        'post_status' => 'any',
                        's' => $reqBody['post_title'],
                    ];

                    $query = new WP_Query($args);
                    $page = $reqBody['page'];

                    if ($query->post_count > 0) {
                        return array_map(function ($post) {
	                        $meta = get_post_meta( $post->ID, 'video-publish', true );

                            return [
                                'post_name' => $post->post_name,
                                'post_title' => $post->post_title,
                                'post_thumbnail' => get_the_post_thumbnail($post),
	                            'post_size' => $meta['size']
                            ];
                        }, array_slice($query->posts, self::LAZY_STEPS * $page, self::LAZY_STEPS));
                    }

                    return [];
                },
            ]);
        });
    }
}