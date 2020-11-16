<?php


namespace IssetBV\VideoPublisher\Wordpress\Widgets;

use IssetBV\VideoPublisher\Wordpress\Plugin;
use IssetBV\VideoPublisher\Wordpress\Renderer;
use IssetBV\VideoPublisher\Wordpress\Service\VideoPublisherService;

class Dashboard extends BaseWidget {
	/**
	 * @var VideoPublisherService
	 */
	private $service;

	public function __construct() {
		$this->service = Plugin::instance()->getVideoPublisherService();
	}

	public function getWidgetId() {
		return 'video-publisher-connect-widget';
	}

	public function getWidgetName() {
		return 'isset.video Dashboard';
	}

	public function execute( $args ) {
		$context['isLoggedIn'] = $this->service->isLoggedIn();

		if ( $context['isLoggedIn'] ) {
			$userInfo = $this->service->getUserInfo();

			$context['user']               = $userInfo;
			$context['logout_url']         = $this->service->getLogoutURL();
			$context['videos_url']         = $context['video_url'] = admin_url( 'admin.php?page=' . Plugin::MENU_MAIN_SLUG );
			$context['stats']              = $this->service->fetchUsage();
			$context['subscription_limit'] = $this->service->fetchSubscriptionLimit();
		} else {
			$context['login_url'] = $this->service->getLoginURL();
		}

		echo Renderer::render( 'admin/dashboard/dashboard.php', $context );
	}
}
