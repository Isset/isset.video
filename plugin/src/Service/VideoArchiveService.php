<?php


namespace IssetBV\VideoPublisher\Wordpress\Service;

use IssetBV\VideoPublisher\Wordpress\Plugin;

class VideoArchiveService extends BaseHttpService {

	const ARCHIVE_TOKEN_SESSION_KEY = 'issetbv-video-archive-token';
	const ARCHIVE_ROOT_SESSION_KEY  = 'issetbv-video-archive-root';
	const ARCHIVE_PLATFORM          = 'archive';

	/** @var Plugin */
	private $plugin;

	/** @var string */
	private $archiveUrl;

	/** @var string */
	private $archiveToken;

	/** @var VideoPublisherService */
	private $videoPublisherService;

	public function __construct( Plugin $plugin ) {
		$this->plugin                = $plugin;
		$this->videoPublisherService = $plugin->getVideoPublisherService();
		$this->archiveUrl            = $this->videoPublisherService->getArchiveURL();
		$this->archiveToken          = $this->getArchiveToken();
	}

	public function getArchiveToken() {
		$token = $this->getTokenFromSession();

		if ( $token === '' ) {
			$token = $this->videoPublisherService->exchangeToken( self::ARCHIVE_PLATFORM );

			$this->storeTokenInSession( $token );
		}

		return $token;
	}

	public function getArchiveRoot() {
		$root = $this->getRootFromSession();

		if ( $root === '' ) {
			$response = $this->getArchiveRootFolder();

			if ( isset( $response['root_folder'] ) ) {
				$root = $response['root_folder'];

				$this->storeRootInSession( $root );
			}
		}

		return $root;
	}

	public function getArchiveUrl() {
		return $this->archiveUrl;
	}

	private function getTokenFromSession() {
		return $this->getValueFromSession( self::ARCHIVE_TOKEN_SESSION_KEY );
	}

	private function getRootFromSession() {
		return $this->getValueFromSession( self::ARCHIVE_ROOT_SESSION_KEY );
	}

	private function storeTokenInSession( $token ) {
		$this->storeValueInSession( self::ARCHIVE_TOKEN_SESSION_KEY, $token );
	}

	private function storeRootInSession( $root ) {
		$this->storeValueInSession( self::ARCHIVE_ROOT_SESSION_KEY, $root );
	}

	private function removeTokenFromSession() {
		$this->removeValueFromSession( self::ARCHIVE_TOKEN_SESSION_KEY );
	}

	private function removeRootFromSession() {
		$this->removeValueFromSession( self::ARCHIVE_ROOT_SESSION_KEY );
	}

	private function getValueFromSession( $key ) {
		if ( isset( $_SESSION ) && isset( $_SESSION[ $key ] ) ) {
			return $_SESSION[ $key ];
		}

		return '';
	}

	private function storeValueInSession( $key, $value ) {
		if ( isset( $_SESSION ) ) {
			$_SESSION[ $key ] = $value;
		}
	}

	private function removeValueFromSession( $key ) {
		if ( isset( $_SESSION ) && isset( $_SESSION[ self::ARCHIVE_TOKEN_SESSION_KEY ] ) ) {
			unset( $_SESSION[ self::ARCHIVE_TOKEN_SESSION_KEY ] );
		}
	}

	public function createArchiveFile( $filename, $url ) {
		return $this->archiveJsonPost(
			'/api/files/create',
			array(
				'folder'   => 'root',
				'filename' => $filename,
				'url'      => $url,
			)
		);
	}

	public function getArchiveRootFolder() {
		return $this->archiveGet( '/api/root' );
	}

	private function archiveGet( $path ) {
		$auth_token = $this->getArchiveToken();

		return $this->get( $this->archiveUrl, $path, $auth_token, self::ARCHIVE_PLATFORM );
	}

	private function archiveDelete( $path ) {
		$auth_token = $this->getArchiveToken();

		return $this->delete( $this->archiveUrl, $path, $auth_token, self::ARCHIVE_PLATFORM );
	}

	private function archiveJsonPost( $path, $data ) {
		$auth_token = $this->getArchiveToken();

		return $this->post( $this->archiveUrl, $path, $auth_token, $data, self::ARCHIVE_PLATFORM );
	}

	public function deleteArchiveFile( $uuid ) {
		return $this->archiveDelete( "/api/files/{$uuid}/delete" );
	}

	public function publishArchiveFile( $uuid, $presets ) {
		return $this->archiveJsonPost(
			"/api/files/{$uuid}/publish",
			array(
				'presets' => $presets,
			)
		);
	}

	public function removeAuthToken() {
		$this->removeTokenFromSession();
		$this->removeRootFromSession();
	}
}
