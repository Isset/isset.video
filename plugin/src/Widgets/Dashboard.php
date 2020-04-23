<?php


namespace IssetBV\VideoPublisher\Wordpress\Widgets;


use IssetBV\VideoPublisher\Wordpress\Plugin;
use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use IssetBV\VideoPublisher\Wordpress\Service\VideoPublisherService;
use Timber\Timber;

class Dashboard extends BaseWidget {
	/**
	 * @var VideoPublisherService
	 */
	private $service;

	public function __construct() {
		$this->service = new VideoPublisherService(Plugin::instance());
	}

	public function getWidgetId() {
		$typeName = VideoPublisher::getTypeName();
		return "{$typeName}-connect-widget";
	}

	public function getWidgetName() {
		return 'isset.video Dashboard';
	}

	public function execute( $args ) {
		$context['isLoggedIn'] = $this->service->isLoggedIn();

		if ($context['isLoggedIn']) {
			$userInfo = $this->service->getUserInfo();

			$context['user']       = $userInfo;
			$context['logout_url'] = $this->service->getLogoutURL();
			$context['videos_url'] = $context['video_url'] = admin_url( 'edit.php?post_type=' . urlencode( VideoPublisher::getTypeName() ) );
			$context['stats'] = $this->service->getStats();
		}
		else {
			$context['login_url'] = $this->service->getLoginURL();
		}

		echo Timber::compile( __DIR__ . '/../../views/admin/dashboard/dashboard.html.twig', $context);
	}
}