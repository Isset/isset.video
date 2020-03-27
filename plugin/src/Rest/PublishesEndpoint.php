<?php

namespace IssetBV\VideoPublisher\Wordpress\Rest;

use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use WP_Query;

class PublishesEndpoint
{
    public static function publishes()
    {
        add_action('rest_api_init', function () {
            register_rest_route('isset-publisher/v1', '/publishes', [
                'methods' => 'GET',
                'callback' => function () {
                    $args = [
                        'post_type' => VideoPublisher::getTypeName(),
                        'post_status' => ['publish'],
                    ];
                    $WP_Query = new WP_Query($args);

                    if ($WP_Query->post_count > 0) {
                        return array_map(function ($post) {
                            return [
                                'post_name' => $post->post_name,
                                'post_title' => $post->post_title,
                                'post_thumbnail' => the_post_thumbnail($post)
                            ];
                        }, $WP_Query->posts);
                    }

                    return [];
                },
            ]);
        });
    }
}