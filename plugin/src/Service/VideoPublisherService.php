<?php


namespace IssetBV\VideoPublisher\Wordpress\Service;


use IssetBV\VideoPublisher\Wordpress\Plugin;
use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use WP_Query;

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
		$auth_token = $this->getAuthToken();
		$response   = wp_remote_get(
			sprintf( rtrim( $this->getPublisherURL(), '/' ) . '/api/publishes/%s', urlencode( $uuid ) ),
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
			if ( $response_code >= 400 && $response_code < 500 ) {
				$this->removeAuthToken();
			}

			return false;
		}

		$body = json_decode( $response['body'], true );
		if ( ! isset( $body['viewable'] ) || ! $body['viewable'] ) {
			return false;
		}

		if ( ! isset( $body['playout'] ) || ! $body['playout'] ) {
			return false;
		}

		return $body['playout']['playout_url'];

	}

	/**
	 * @return bool
	 */
	public function getPublishedVideos() {
		$auth_token = $this->getAuthToken();

		if ( $auth_token === false ) {
			return false;
		}

		$response = wp_remote_get(
			sprintf( rtrim( $this->getPublisherURL(), '/' ) . '/api/publishes?size=100' ),
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
			if ( $response_code >= 400 && $response_code < 500 ) {
				$this->removeAuthToken();
			}

			return false;
		}

		$thumbnail_service = $this->plugin->getThumbnailService();
		$result            = json_decode( $response['body'], true );

		if ( is_array( $result['results'] ) ) {
			foreach ( $result['results'] as $publish ) {
				if ( empty( $publish['uuid'] ) ) {
					continue;
				}

				if ( $publish['status'] === 'online' && (int) $publish['enabled'] === 1 ) {
					$post_status = 'publish';
				} else {
					$post_status = 'draft';
				}

				$args     = [
					'post_type'   => VideoPublisher::getTypeName(),
					'name'        => $publish['uuid'],
					'post_status' => [ 'publish', 'draft' ],
				];
				$WP_Query = new WP_Query( $args );

				$post_data = [
					'post_name'   => $publish['uuid'],
					'post_type'   => VideoPublisher::getTypeName(),
					'post_status' => $post_status,
				];

				if ( $WP_Query->post_count === 0 ) {
					$post_data['post_title'] = $publish['description'];

					$post_id = wp_insert_post( $post_data, true );
					$post    = get_post( $post_id );

					$thumbnail_service->setThumbnail( $post_id, $publish['assets'][0]['url'] );
				} else {
					$post = $WP_Query->next_post();

					$post_data['ID'] = $post->ID;
					wp_update_post( $post_data );
				}

				update_post_meta( $post->ID, 'video-publish', $publish );
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
}