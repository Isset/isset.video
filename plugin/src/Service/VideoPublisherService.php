<?php


namespace IssetBV\VideoPublisher\Wordpress\Service;

use DateTime;
use IssetBV\VideoPublisher\Wordpress\Plugin;

class VideoPublisherService extends BaseHttpService {
	const PRESET_720P        = '720p';
	const PRESET_1080P       = '1080p';
	const PRESET_4K          = '4k';
	const PUBLISHER_PLATFORM = 'publisher';

	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * @var array
	 */
	private $issetVideoPublisherOptions;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * VideoPublisherService constructor.
	 *
	 * @param $plugin
	 */
	public function __construct( Plugin $plugin, $config = array() ) {
		$this->plugin = $plugin;
		$this->config = $config;
	}

	public function getMyIssetVideoURL() {
		return $this->getConfig( 'my_isset_video_url' );
	}

	public function getArchiveURL() {
		return $this->getConfig( 'archive_url' );
	}

	public function getPublisherURL() {
		return $this->getConfig( 'publisher_url' );
	}

	public function getUploaderURL() {
		return $this->getConfig( 'uploader_url' );
	}

	public function getMercureURL() {
		return $this->getConfig( 'mercure_url' );
	}

	public function getLoginURL() {
		$url = $this->getMyIssetVideoURL();

		return rtrim( $url, '/' ) .
			   '/publisher-token-request?referrer=' .
			   urlencode( add_query_arg( array( 'ivp-action' => 'auth' ), site_url( 'index.php' ) ) );
	}

	public function getLogoutURL() {
		return add_query_arg(
			array(
				'ivp-action' => 'deauth',
			),
			site_url( 'index.php' )
		);
	}

	public function updateAuthToken( $token ) {
		update_option( 'isset-video-publisher-auth-token', $token, true );
		$this->flushUserInfo();
	}

	public function removeAuthToken() {
		delete_option( 'isset-video-publisher-auth-token' );
		$this->flushUserInfo();
	}

	public function getPublisherToken() {
		return $this->getAuthToken();
	}

	public function shouldShowAdvancedOptions() {
		if ( isset( $_GET['advanced'] ) && $_GET['advanced'] === 'true' ) {
			return true;
		}

		return $this->getOption( 'show_advanced_options', false );
	}

	private function saveOptions() {
		$this->initOptions();
		update_option( 'isset-video-publisher-options', $this->issetVideoPublisherOptions, false );
	}

	private function initOptions() {
		if ( null === $this->issetVideoPublisherOptions ) {
			$this->issetVideoPublisherOptions = get_option( 'isset-video-publisher-options' );
		}
	}

	private function getOption( $name, $default = null ) {
		$this->initOptions();

		if ( isset( $this->issetVideoPublisherOptions[ $name ] ) ) {
			return $this->issetVideoPublisherOptions[ $name ];
		}

		return $default;
	}

	private function setOption( $name, $value ) {
		$this->initOptions();
		$this->issetVideoPublisherOptions[ $name ] = $value;
	}

	public function flushUserInfo() {
		$this->setOption( 'user-info', false );
		$this->saveOptions();
	}

	public function getUserInfo() {
		if ( ! $this->isLoggedIn() ) {
			return false;
		}

		$info = $this->getOption( 'user-info', false );
		if ( $info === false ) {
			$info = $this->fetchUserInfo();
			$this->setOption( 'user-info', $info );
			$this->saveOptions();
		}

		return $info;
	}

	private function getAuthToken() {
		return get_option( 'isset-video-publisher-auth-token' );
	}

	public function getVideoUrlForWordpress( $uuid ) {
		return add_query_arg(
			array(
				'uuid'       => $uuid,
				'ivp-action' => 'video-redirect',
			),
			site_url( 'index.php' )
		);
	}

	public function getVideoUrl( $uuid ) {
		$body = $this->fetchPublishInfo( $uuid );

		if ( ! isset( $body['playout'] ) || ! $body['playout'] || ! isset( $body['playout'] ['playout_url'] )
			 || ! $body['playout']['playout_url']
		) {
			return false;
		}

		return $body['playout']['playout_url'];
	}

	public function fetchPublishInfo( $uuid ) {
		return $this->publisherGet( '/api/publishes/' . urlencode( $uuid ) . '?clientIp=' . $this->getClientIp() );
	}

	public function isLoggedIn() {
		return false !== $this->getAuthToken();
	}

	private function fetchUserInfo() {
		return $this->myIssetGet( '/api/token/account' );
	}

	public function logout() {
		if ( ! $this->isLoggedIn() ) {
			return;
		}

		$this->myIssetDelete( '/api/token/delete/' );

		error_log( 'Manual logout' );
		$this->removeAuthToken();
	}

	protected function myIssetGet( $path ) {
		$auth_token = $this->getAuthToken();

		return $this->get( $this->getMyIssetVideoURL(), $path, $auth_token, self::PUBLISHER_PLATFORM );
	}

	protected function myIssetDelete( $path ) {
		$auth_token = $this->getAuthToken();

		return $this->delete( $this->getMyIssetVideoURL(), $path, $auth_token, self::PUBLISHER_PLATFORM );
	}

	public function getUploadURL() {
		$result = $this->publisherGet( '/api/uploads/request-url' );

		return $result['url'];
	}

	private function publisherGet( $path ) {
		$auth_token = $this->getAuthToken();

		return $this->get( $this->getPublisherURL(), $path, $auth_token, self::PUBLISHER_PLATFORM );
	}

	private function issetVideoGet( $path ) {
		return $this->myIssetGet( $path );
	}

	public function fetchStats() {
		return $this->publisherGet( '/api/statistics/user/streaming' );
	}

	public function fetchUsage() {
		return $this->publisherGet( '/api/statistics/user/usage' );
	}

	public function fetchSubscriptionLimit() {
		return $this->issetVideoGet( '/api/token/subscription-limit' );
	}

	public function uploadingAllowed() {
		$limit   = $this->fetchSubscriptionLimit();
		$current = $this->fetchUsage();

		return $limit['storage_limit'] > $current['storage'];
	}

	public function fetchStatsV2( DateTime $dateFrom ) {
		return $this->publisherGet( "/api/statistics/user/streaming?dateFrom={$dateFrom->format('Y-m-d')}" );
	}

	public function exchangeToken( $platform ) {
		return $this->issetVideoGet( '/api/token/platform/' . $platform )['token'];
	}

	public function fetchUserSettings() {
		return $this->issetVideoGet( '/api/token/subscription' );
	}

	public function getPresetList() {
		$subscription = $this->fetchUserSettings();
		$presets      = array();

		if ( $subscription ) {
			$allPresets = array( self::PRESET_720P, self::PRESET_1080P, self::PRESET_4K );

			foreach ( $allPresets as $preset ) {
				$presets[] = $preset;

				if ( $preset === $subscription['subscription_maximum_quality'] || $preset === $subscription['preferred_maximum_quality'] ) {
					return $presets;
				}
			}
		}

		return $presets;
	}

	protected function getClientIp() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return $ip;
	}

	private function getConfig( $key ) {
		return isset( $this->config[ $key ] ) ? $this->config[ $key ] : null;
	}

	public function getLivestreamDetails( $uuid ) {
		return $this->publisherGet( '/api/livestreams/' . urlencode( $uuid ) . "?clientIp={$this->getClientIp()}" );
	}
}
