<?php

/*
Plugin Name: Isset Video Publisher plugin
Plugin URI: http://isset.nl
Description: Video intergration from the video publisher from Isset Internet Professionals
Version: 0.1.0
Author: Casper Houde
*/
require_once (dirname(__file__) . '/inc/class.isset-video-publisher.php');
$issetVideo = IssetVideoPublisher::instance();

if(is_admin()) {
    require_once (dirname(__file__) . '/inc/class.admin-page.php');
    $adminPage = IssetVideoPublisherAdminPage::instance();
}
