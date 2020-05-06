<?php
/*
Plugin Name:
Plugin URI: https://isset.video/wp-plugin/
Description: Wordpress isset.video implementation
Author: isset.video
Author URI: http://isset.nl/
License: GPL v3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
Version: 0.1.0
Text Domain: isset.video
Domain Path: /languages
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