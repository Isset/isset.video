<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Settings;

use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;
use IssetBV\VideoPublisher\Wordpress\Renderer;

class Menu extends BaseAction {
	public function isAdminOnly() {
		return true;
	}

	function execute( $arguments ) {
		add_options_page(
			__( 'isset.video settings', 'isset-video-publisher' ),
			__( 'isset.video settings', 'isset-video-publisher' ),
			'manage_options',
			'isset-video-publisher-admin',
			function () {
				$vps     = $this->plugin->getVideoPublisherService();
				$context = array();

				$context['logged_in'] = $vps->isLoggedIn();

				if ( $context['logged_in'] ) {
					$context['user']       = $vps->getUserInfo();
					$context['logout_url'] = $vps->getLogoutURL();
				}

				$context['login_url'] = $vps->getLoginURL();
				$context['video_url'] = $this->plugin->getOverviewPageUrl();

				echo Renderer::render( 'admin/page.php', $context );
			}
		);
	}

	function getAction() {
		return 'admin_menu';
	}
}
