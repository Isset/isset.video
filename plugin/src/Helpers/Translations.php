<?php

function isset_video_load_default_textdomain( $mofile, $domain ) {
	if ( 'isset-video' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
		$locale = determine_locale();
		if ( $locale !== 'nl_NL' && $locale !== 'en_US' ) {
			$locale = 'en_US';
		}

		$mofile = ISSET_VIDEO_PUBLISHER_PATH . 'languages/' . $domain . '-' . $locale . '.mo';
	}

	return $mofile;
}
