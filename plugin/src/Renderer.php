<?php

namespace IssetBV\VideoPublisher\Wordpress;

class Renderer {

	/**
	 * @param string $fileName
	 * @param array  $data
	 *
	 * @return false|string
	 */
	static function render( $fileName, $data = array() ) {
		$path = ISSET_VIDEO_PUBLISHER_PATH . 'views/' . $fileName;
		extract( $data );

		ob_start();
		include $path;
		return ob_get_clean();
	}
}
