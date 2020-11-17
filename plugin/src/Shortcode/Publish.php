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

		$publishService = $this->plugin->getVideoPublisherService();
		$publish        = $publishService->fetchPublishInfo( $uuid );

		if ( ! $publish ) {
			return Renderer::render( 'shortcode/publish-invalid.php' );
		}

		$video_url = $publishService->getVideoUrlForWordpress( $uuid );

		$context              = $attr;
		$context['poster']    = isset( $publish['assets'] ) ? $this->findDefaultImage( $publish['assets'] ) : null;
		$context['uuid']      = $uuid;
		$context['video_url'] = $video_url;

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
