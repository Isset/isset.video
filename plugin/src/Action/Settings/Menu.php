<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Settings;


use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;
use Timber\Timber;

class Menu extends BaseAction {
	public function isAdminOnly() {
		return true;
	}

	function execute( $arguments ) {
		add_options_page(
			__( 'Video Publisher Settings', 'isset-video-publisher' ),
			__( 'Video Publisher Settings', 'isset-video-publisher' ),
			'manage_options',
			'isset-video-publisher-admin',
			function () {
				Timber::render( __DIR__ . '/../../../views/admin/page.html.twig', Timber::context() );
			}
		);
	}

	function getAction() {
		return 'admin_menu';
	}
}