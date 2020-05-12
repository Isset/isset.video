<?php

namespace IssetBV\VideoPublisher\Wordpress\Rest;

use DateTime;
use IssetBV\VideoPublisher\Wordpress\Plugin;
use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use Timber\Timber;

class DashboardEndpoint {
	public static function dashboard() {
		add_action( 'rest_api_init', function () {
			register_rest_route( 'isset-publisher/v1', '/dashboard', [
				'methods'  => 'GET',
				'callback' => function ( $request ) {
					$service = Plugin::instance()->getVideoPublisherService();

					$context['isLoggedIn'] = $service->isLoggedIn();

					if ( $context['isLoggedIn'] ) {
						$userInfo = $service->getUserInfo();

						$context['user']               = $userInfo;
						$context['logout_url']         = $service->getLogoutURL();
						$context['videos_url']         = $context['video_url'] = admin_url( 'edit.php?post_type=' . urlencode( VideoPublisher::getTypeName() ) );
						$context['stats']              = $service->fetchStats();
						$context['subscription_limit'] = $service->fetchSubscriptionLimit();
						$context['streaming_stats']    = $service->fetchStatsV2( new DateTime( '-1 week' ) );
					} else {
						$context['login_url'] = $service->getLoginURL();
					}

					return [
						'html' => Timber::compile( __DIR__ . '/../../views/admin/dashboard/api-dashboard.html.twig', $context )
					];
				},
			] );
		} );
	}
}