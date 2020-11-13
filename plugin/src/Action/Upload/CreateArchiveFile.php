<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Upload;

use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;

class CreateArchiveFile extends BaseAction {

	public function isAdminOnly() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	function execute( $arguments ) {
		check_ajax_referer( 'isset-video' );
		header( 'Content-Type', 'application/json' );

		if ( array_key_exists( 'filename', $_POST ) && array_key_exists( 'url', $_POST ) ) {
			$service     = $this->plugin->getVideoArchiveService();
			$archiveFile = $service->createArchiveFile( $_POST['filename'], $_POST['url'] );

			if ( $archiveFile ) {
				$this->publishFile( $archiveFile );
			}

			exit( json_encode( $archiveFile ) );
		}
	}

	function getAction() {
		return 'wp_ajax_isset-video-create-archive-file';
	}

	private function publishFile( $archiveFile ) {
		$publisherService = $this->plugin->getVideoPublisherService();
		$archiveService   = $this->plugin->getVideoArchiveService();

		$presets = $publisherService->getPresetList();
		$archiveService->publishArchiveFile( $archiveFile['uuid'], $presets );
	}
}
