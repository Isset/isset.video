<?php


namespace IssetBV\VideoPublisher\Wordpress\Service;

class TextService {
	public static function validateAndSanitizeText( $value ) {
		if ( isset( $_POST['id'] ) && is_string( $_POST['id'] ) ) {
			return sanitize_text_field($value);
		}
		return false;
	}
}