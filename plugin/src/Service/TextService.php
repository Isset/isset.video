<?php


namespace IssetBV\VideoPublisher\Wordpress\Service;

class TextService {
	public static function validateAndSanitizeText( $data, $key ) {
		if ( isset( $data[ $key ] ) && is_string( $data[ $key ] ) ) {
			return sanitize_text_field( $data[ $key ] );
		}
		return false;
	}
}
