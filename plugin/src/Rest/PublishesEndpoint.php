<?php

namespace IssetBV\VideoPublisher\Wordpress\Rest;

use IssetBV\VideoPublisher\Wordpress\Plugin;
use IssetBV\VideoPublisher\Wordpress\Service\VideoPublisherService;

class PublishesEndpoint
{
    public static function publishes()
    {
        add_action( 'rest_api_init', function () {
            register_rest_route( 'isset-publisher/v1', '/publishes', [
                'methods' => 'GET',
                'callback' => function() {
                    $service = new VideoPublisherService(Plugin::instance());
                    return $service->getPublishes();
                },
            ]);
        } );
    }
}