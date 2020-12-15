<?php


namespace IssetBV\VideoPublisher\Wordpress\Shortcode;

use IssetBV\VideoPublisher\Wordpress\Renderer;

class Livestream extends ShortcodeBase {
	const CODE = 'isset-livestream';

	function generate( $params, $content = null ) {
		$attr = shortcode_atts(
			array(
				'uuid'    => false,
				'post_id' => false,
			),
			$params
		);

		$uuid = $attr['uuid'];

		$publishService = $this->plugin->getVideoPublisherService();
		$details        = $publishService->getLivestreamDetails( $uuid );

		if ( ! $details ) {
			return Renderer::render( 'shortcode/livestream-invalid.php' );
		}

		$context            = $attr;
		$context['uuid']    = $uuid;
		$context['url']     = $details['playout_url'];
		$context['socket']  = $this->getSocketUrl( $uuid );
		$context['started'] = $details['date_started'] !== null;
		$context['ended']   = $details['date_ended'] !== null;

		return Renderer::render( 'shortcode/livestream.php', $context );
	}

	function getSocketUrl( $uuid ) {
		$publishService = $this->plugin->getVideoPublisherService();
		$mercureUrl     = $publishService->getMercureURL();

		return "{$mercureUrl}?topic=" . urlencode( "https://isset.video/livestreams/{$uuid}" );
	}
}
