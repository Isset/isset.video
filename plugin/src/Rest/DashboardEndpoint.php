<?php

namespace IssetBV\VideoPublisher\Wordpress\Rest;

use DateTime;
use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use Timber\Timber;

class DashboardEndpoint extends BaseEndpoint {
	function getRoute() {
		return '/dashboard';
	}

	function execute( $request ) {
		$service = $this->plugin->getVideoPublisherService();

		$context['isLoggedIn'] = $service->isLoggedIn();

		if ( $context['isLoggedIn'] ) {
			$userInfo = $service->getUserInfo();
			$stats    = $service->fetchStatsV2( new DateTime( '-1 week' ) );

			$context['user']               = $userInfo;
			$context['logout_url']         = $service->getLogoutURL();
			$context['videos_url']         = admin_url( 'edit.php?post_type=' . urlencode( VideoPublisher::getTypeName() ) );
			$context['stats']              = $service->fetchStats();
			$context['subscription_limit'] = $service->fetchSubscriptionLimit();
			$context['streaming_stats']    = $stats;
			$context['isset_video_url']    = $service->getMyIssetVideoURL();
		} else {
			$context['login_url'] = $service->getLoginURL();
		}

		return [
			'html'  => Timber::compile( __DIR__ . '/../../views/admin/dashboard/api-dashboard.html.twig', $context ),
			'stats' => $stats
		];
	}
}