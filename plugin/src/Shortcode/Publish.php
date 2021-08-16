<?php


namespace IssetBV\VideoPublisher\Wordpress\Shortcode;

use IssetBV\VideoPublisher\Wordpress\Renderer;

class Publish extends ShortcodeBase {
	const CODE = 'publish';

	function generate( $params, $content = null ) {
		// If we have a video, attach chrome cast framework
		wp_enqueue_script(
			'chrome_cast',
			'https://www.gstatic.com/cv/js/sender/v1/cast_sender.js?loadCastFramework=1'
		);
		// Also attach google ima sdk
		wp_enqueue_script(
			'google-ima-sdk',
			'//imasdk.googleapis.com/js/sdkloader/ima3.js'
		);

		$attr = shortcode_atts(
			array(
				'uuid'     => false,
				'post_id'  => false,
				'poster'   => false,
				'controls' => 'controls',
				'autoplay' => '',
				'loop'     => '',
				'muted'    => '',
			),
			$params
		);

		$uuid = $attr['uuid'];

		$publishService   = $this->plugin->getVideoPublisherService();
		$publish          = $publishService->fetchPublishInfo( $uuid );
		$advertisementUrl = $publishService->getAdvertisementUrl( $uuid );

		if ( ! $publish ) {
			return Renderer::render( 'shortcode/publish-invalid.php' );
		}

		$video_url = $publishService->getVideoUrlForWordpress( $uuid );

		$context              = $attr;
		$context['poster']    = isset( $publish['assets'] ) ? $this->findDefaultImage( $publish['assets'] ) : null;
		$context['uuid']      = $uuid;
		$context['subtitles'] = isset( $publish['subtitles'] ) ? $publish['subtitles'] : array();
		$context['chapters']  = isset( $publish['chapters'] ) ? $publish['chapters'] : array();
		$context['video_url'] = $video_url;
		$context['ad_url']    = $advertisementUrl;
		$context['css_url']   = $publishService->getPublisherURL() . "/css/publish/{$uuid}/player.css?time=" . time();

		return Renderer::render( 'shortcode/publish.php', $context );
	}

	private function findDefaultImage( $assets ) {
		if ( is_array( $assets ) && count( $assets ) > 0 ) {
			foreach ( $assets as $asset ) {
				if ( $asset['is_default'] ) {
					return $asset['url'];
				}
			}

			return $assets[0]['url'];
		}

		return '';
	}
}
