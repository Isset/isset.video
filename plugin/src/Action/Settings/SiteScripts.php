<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Settings;

use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;

class SiteScripts extends BaseAction {

	public function isSiteOnly() {
		return true;
	}

	function execute( $arguments ) {
		wp_localize_script(
			'isset-video-main',
			'IssetVideoPublisherAjax',
			array(
				'nonce' => wp_create_nonce( 'isset-video' ),
			)
		);
	}

	function getAction() {
		return 'admin_enqueue_scripts';
	}
}
