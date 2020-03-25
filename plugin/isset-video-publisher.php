<?php

/*
Plugin Name: Isset Video Publisher plugin
Plugin URI: http://isset.nl
Description: Video integration from the video publisher from Isset Internet Professionals
Version: 0.1.0
Author: Isset
*/

use IssetBV\VideoPublisher\Wordpress\Autoloader;
use IssetBV\VideoPublisher\Wordpress\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 1 );
}

define( 'ISSET_VIDEO_PUBLISHER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ISSET_VIDEO_PUBLISHER_URL', plugin_dir_url( __FILE__ ) );
define( 'ISSET_VIDEO_PUBLISHER_VERSION', '0.1.0' );

include_once __DIR__ . '/src/Autoloader.php';

spl_autoload_register( new Autoloader() );

add_action( 'init', function () {
	Plugin::instance()->init();

    wp_register_script(
        'publisher-video',
        plugins_url( 'js/publisher-video-block.js', __FILE__ ),
        ['wp-blocks', 'wp-element', 'wp-editor']
    );

    register_block_type( 'publisher/video', [
        'editor_script' => 'publisher-video',
    ]);
} );

add_action( 'rest_api_init', function () {
    register_rest_route( 'isset-publisher/v1', '/publishes', array(
        'methods' => 'GET',
        'callback' => function() {
            global $wpdb;

            $service = new \IssetBV\VideoPublisher\Wordpress\Service\VideoPublisherService(Plugin::instance());

            return $service->getPublishes();
        },
    ) );
} );