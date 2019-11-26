<?php


namespace IssetBV\VideoPublisher\Wordpress\Entrypoint;


class VideoRedirect extends BaseEntrypoint {
	function enter() {
		$service = $this->plugin->getVideoPublisherService();
		$uuid    = $_GET['uuid'] ?: null;

		if ( $uuid === null ) {
			header( 'Status: 404' );

			return;
		}

		$url = $service->getVideoUrl( $uuid );

		if ( ! $url ) {
			header( 'Status: 404' );

			return;
		}

		header( 'HTTP/1.1 302 Temporary Redirect', true );
		header( 'Content-Type: ', true );
		header( 'Location: ' . $url );

		return;
	}

}