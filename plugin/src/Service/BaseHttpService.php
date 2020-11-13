<?php


namespace IssetBV\VideoPublisher\Wordpress\Service;

use WP_Http;

abstract class BaseHttpService {

	abstract public function removeAuthToken();

	protected function get( $baseUrl, $path, $token, $platform ) {
		if ( $token === false ) {
			return false;
		}

		$url      = rtrim( $baseUrl, '/' ) . $path;
		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'x-token-auth'     => $token,
					'x-token-platform' => $platform,
				),
			)
		);

		if ( ! $this->isResponseValid( 'GET', $url, $response ) ) {
			return false;
		}

		return json_decode( $response['body'], true );
	}

	protected function post( $baseUrl, $path, $token, $data, $platform ) {
		if ( $token === false ) {
			return false;
		}

		$url      = rtrim( $baseUrl, '/' ) . $path;
		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'x-token-auth'     => $token,
					'x-token-platform' => $platform,
				),
				'body'    => wp_json_encode( $data ),
			)
		);

		if ( ! $this->isResponseValid( 'POST', $url, $response ) ) {
			return false;
		}

		return json_decode( $response['body'], true );
	}

	protected function delete( $baseUrl, $path, $token, $platform ) {
		if ( $token === false ) {
			return false;
		}

		$client   = new WP_Http();
		$url      = rtrim( $baseUrl, '/' ) . $path;
		$response = $client->request(
			$url,
			array(
				'method'  => 'DELETE',
				'headers' => array(
					'x-token-auth'     => $token,
					'x-token-platform' => $platform,
				),
			)
		);

		if ( ! $this->isResponseValid( 'DELETE', $url, $response ) ) {
			return false;
		}

		return true;
	}

	public function isResponseValid( $method, $url, $response ) {
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code === 200 ) {
			return true;
		}

		if ( $response_code === 401 || $response_code === 403 ) {
			error_log( "Logged out for $method $url [$response_code]" );
			$this->removeAuthToken();
		}

		return true;
	}
}
