<?php


namespace IssetBV\VideoPublisher\Wordpress\Service;


use IssetBV\VideoPublisher\Wordpress\Plugin;

class VideoPublisherService {
	/**
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * @var array
	 */
	private $issetVideoPublisherOptions;

	/**
	 * VideoPublisherService constructor.
	 *
	 * @param $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function getMyIssetVideoURL() {
		return $this->getOption( "my_isset_video_url", Plugin::MY_ISSET_VIDEO_URL );
	}

	public function getLoginURL() {
		$url = $this->getMyIssetVideoURL();

		return rtrim( $url, "/" ) . "/publisher-token-request?referrer=" . urlencode( add_query_arg(
					[
						'ivp-action' => 'auth'
					],
					site_url( 'index.php' )
				)
			);
	}

	public function getLogoutURL() {
		return add_query_arg(
			[
				'ivp-action' => 'deauth'
			],
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

	public function getPublisherURL() {
		return $this->getOption( 'publisher_url', Plugin::PUBLISHER_URL );
	}

	public function shouldShowAdvancedOptions() {
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
			[
				'uuid'       => $uuid,
				'ivp-action' => 'video-redirect'
			],
			site_url( 'index.php' )
		);
	}

	public function getVideoUrl( $uuid ) {
		$body = $this->fetchPublishInfo( $uuid );

		if ( ! isset( $body['playout'] ) || ! $body['playout'] || ! isset( $body['playout'] ['playout_url'] ) || ! $body['playout']['playout_url'] ) {
			return false;
		}

		return $body['playout']['playout_url'];

	}

	public function fetchPublishInfo( $uuid ) {
		return $this->publisherGet( '/api/publishes/' . urlencode( $uuid ) );
	}

	/**
	 * @return bool
	 */
	public function getPublishedVideos() {
		$result            = $this->publisherGet( '/api/publishes?size=100' );
		$wordpress_service = $this->plugin->getWordpressService();

		if ( is_array( $result['results'] ) ) {
			foreach ( $result['results'] as $publish ) {
				if ( empty( $publish['uuid'] ) ) {
					continue;
				}

				$wordpress_service->updatePostFromPublish( $publish );
			}
		}

		return false;
	}

	public function isLoggedIn() {
		return false !== $this->getAuthToken();
	}

	private function fetchUserInfo() {
		$auth_token = $this->getAuthToken();

		if ( $auth_token === false ) {
			return false;
		}

		$response = wp_remote_get(
			sprintf( rtrim( $this->getMyIssetVideoURL(), '/' ) . '/api/token/account' ),
			[
				'headers' => [
					'x-token-auth'     => $auth_token,
					'x-token-platform' => 'publisher'
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			if ( $response_code >= 400 && $response_code < 500 ) {
				$this->removeAuthToken();
			}

			return false;
		}

		return json_decode( $response['body'], true );
	}

	public function logout() {
		if ( ! $this->isLoggedIn() ) {
			return;
		}

		$auth_token = $this->getAuthToken();

		if ( $auth_token === false ) {
			return;
		}

		wp_remote_request(
			sprintf( rtrim( $this->getMyIssetVideoURL(), '/' ) . '/api/token/delete/' . urlencode( $this->getAuthToken() ) ),
			[
				'method'  => 'DELETE',
				'headers' => [
					'x-token-auth'     => $auth_token,
					'x-token-platform' => 'publisher'
				],
			]
		);

		$this->removeAuthToken();
	}

	public function getUploadURL() {
		$result = $this->publisherGet( '/api/uploads/request-url' );

		return $result['url'];
	}

	private function publisherGet( $path ) {
		$auth_token = $this->getAuthToken();

		if ( $auth_token === false ) {
			return false;
		}

		$response = wp_remote_get(
			sprintf( rtrim( $this->getPublisherURL(), '/' ) . $path ),
			[
				'headers' => [
					'x-token-auth' => $auth_token,
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			if ( $response_code === 401 || $response_code === 403 ) {
				$this->removeAuthToken();
			}

			return false;
		}

		return json_decode( $response['body'], true );
	}

	public function fetchUploadInfo( $id ) {
		return $this->publisherGet( '/api/uploads/' . urlencode( $id ) . '/status' );
	}

	public function updateUpload( $post_id ) {
		$status = get_post_meta( $post_id, 'video-isset-status', true );

		if ( $status !== 'transcoding' ) {
			return $status;
		}

		$transcode = get_post_meta( $post_id, 'video-isset-transcode', true );

		$data = $this->fetchUploadInfo( $transcode['id'] );

		if ( $data['status'] === 'publishOnline' ) {
			wp_update_post( [
				'post_name' => $data['publish'],
				'ID'        => $post_id,
			] );

			$publish_info = $this->fetchPublishInfo( $data['publish'] );
			if ( $publish_info === false ) {
				return false;
			}

			$this->plugin->getWordpressService()->updatePostFromPublish( $publish_info, true );
		}

		return get_post_meta( $post_id, 'video-isset-status', true );
	}

	public function updateUploads() {
		$drafts = $this->plugin->getWordpressService()->getUploadDrafts();
		foreach ( $drafts as $draft ) {
			$this->updateUpload( $draft );
		}
	}
}