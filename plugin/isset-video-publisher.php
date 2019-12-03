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
} );
