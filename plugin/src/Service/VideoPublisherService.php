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
	 * @var void
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

	/**
	 *
	 */
	private function initOptions() {
		$this->issetVideoPublisherOptions = get_option( 'isset-video-publisher-options' );
	}

	/**
	 * @return string
	 */
	public function getConsumerKey() {
		if ( null === $this->issetVideoPublisherOptions ) {
			$this->initOptions();
		}

		if ( isset( $this->issetVideoPublisherOptions['consumer_key'] ) ) {
			return $this->issetVideoPublisherOptions['consumer_key'];
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function getPrivateKey() {
		if ( null === $this->issetVideoPublisherOptions ) {
			$this->initOptions();
		}

		if ( isset( $this->issetVideoPublisherOptions['private_key'] ) ) {
			return $this->issetVideoPublisherOptions['private_key'];
		}

		return '';
	}

	private function getToken() {
		$time     = time();
		$response = wp_remote_post(
			Plugin::PUBLISHER_URL . '/api/login',
			[
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body'    => json_encode(
					[
						'consumer_key' => $this->getConsumerKey(),
						'time'         => $time,
						'hash'         => crypt( $time . '' . $this->getPrivateKey() . '' . $this->getConsumerKey(), '$6$rounds=9001$' . $this->getConsumerKey() . '$' ),
					]
				),
			]
		);

		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$body = $response['body'];
			$data = json_decode( $body );
			update_option( Plugin::PUBLISHER_TOKEN_KEY, $data->token );
		}
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
		$xauth_token = get_option( Plugin::PUBLISHER_TOKEN_KEY );
		$response    = wp_remote_get(
			sprintf( Plugin::PUBLISHER_URL . '/api/publish/%s', $uuid ),
			[
				'headers' => [
					'xauth-token' => $xauth_token,
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code >= 400 && $response_code < 500 ) {
			update_option( Plugin::PUBLISHER_TOKEN_KEY, false );
			$this->getToken();

			$xauth_token = get_option( Plugin::PUBLISHER_TOKEN_KEY );
			if ( $xauth_token ) {
				return $this->getVideoUrl( $uuid );
			}
		}

		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$body = json_decode( $response['body'] );
			if ( $body->viewable ) {
				return $body->view->playout_url;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function getPublishedVideos() {
		$this->getToken();

		$xauth_token = get_option( Plugin::PUBLISHER_TOKEN_KEY );
		$response    = wp_remote_get(
			sprintf( Plugin::PUBLISHER_URL . '/api/published' ),
			[
				'headers' => [
					'xauth-token' => $xauth_token,
				],
			]
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( wp_remote_retrieve_response_code( $response ) == 403 ) {
			update_option( Plugin::PUBLISHER_TOKEN_KEY, false );
			$this->getToken();

			$xauth_token = get_option( Plugin::PUBLISHER_TOKEN_KEY );
			if ( $xauth_token ) {
				return $this->getPublishedVideos();
			}
		}

		if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
			$publishes = json_decode( $response['body'], true );

			if ( is_array( $publishes ) ) {
				foreach ( $publishes as $publish ) {
					if ( empty( $publish['uuid'] ) ) {
						continue;
					}

					if ( $publish['status'] === 'online' && (int) $publish['enabled'] === 1 ) {
						$postStatus = 'publish';
					} else {
						$postStatus = 'draft';
					}

					$args     = [
						'post_type'   => VideoPublisher::getTypeName(),
						'name'        => $publish['uuid'],
						'post_status' => [ 'publish', 'draft' ],
					];
					$WP_Query = new WP_Query( $args );

					$postData = [
						'post_name'   => $publish['uuid'],
						'post_type'   => VideoPublisher::getTypeName(),
						'post_status' => $postStatus,
					];

					if ( $WP_Query->post_count === 0 ) {
						$postData['post_title'] = $publish['streamName'];

						$postId = wp_insert_post( $postData, true );
						$post   = get_post( $postId );
					} else {
						$post = $WP_Query->next_post();

						$postData['ID'] = $post->ID;
						wp_update_post( $postData );
					}

					update_post_meta( $post->ID, 'video-publish', $publish );
				}
			}
		}

		return false;
	}

	public function getPublishes()
    {
        $this->getToken();

        $xauth_token = get_option( Plugin::PUBLISHER_TOKEN_KEY );
        $response    = wp_remote_get(
            sprintf( Plugin::PUBLISHER_URL . '/api/published?size=20' ),
            [
                'headers' => [
                    'xauth-token' => $xauth_token,
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
            return json_decode( $response['body'], true );
        }

        if ( wp_remote_retrieve_response_code( $response ) == 403 ) {
            update_option( Plugin::PUBLISHER_TOKEN_KEY, false );
            $this->getToken();

            $xauth_token = get_option( Plugin::PUBLISHER_TOKEN_KEY );
            if ( $xauth_token ) {
                return $this->getPublishes();
            }
        }

        return false;
    }
}