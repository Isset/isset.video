<?php

/*
Plugin Name: isset.video
Plugin URI: https://isset.video/wp-plugin/
Description: Wordpress isset.video implementation
Version: 0.7.3
Requires at least: 5.0
Required PHP: 7.1
Author: isset.video
Author URI: http://isset.nl/
License: GPL v3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
*/

use IssetBV\VideoPublisher\Wordpress\Autoloader;
use IssetBV\VideoPublisher\Wordpress\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 1 );
}

define( 'ISSET_VIDEO_PUBLISHER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ISSET_VIDEO_PUBLISHER_URL', plugin_dir_url( __FILE__ ) );
define( 'ISSET_VIDEO_PUBLISHER_VERSION', '0.7.3' );


include_once __DIR__ . '/src/Autoloader.php';

spl_autoload_register( new Autoloader() );

add_action( 'init', function () {
	Plugin::instance()->init();
} );

add_action( 'admin_menu', function () {
    Plugin::instance()->addMenuItems();
} );