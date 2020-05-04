<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Settings;


use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;
use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use Timber\Timber;

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
				$context = Timber::context();
				$vps     = $this->plugin->getVideoPublisherService();

				$context['logged_in'] = $vps->isLoggedIn();

				if ( $context['logged_in'] ) {
					$context['user']       = $vps->getUserInfo();
					$context['logout_url'] = $vps->getLogoutURL();
				}

				$context['login_url']              = $vps->getLoginURL();
				$context['show_advanced_settings'] = $vps->shouldShowAdvancedOptions();

				$context['video_url'] = admin_url( 'edit.php?post_type=' . urlencode( VideoPublisher::getTypeName() ) );

				Timber::render( __DIR__ . '/../../../views/admin/page.html.twig', $context );
			}
		);
	}

	function getAction() {
		return 'admin_menu';
	}
}
