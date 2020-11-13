<?php

namespace IssetBV\VideoPublisher\Wordpress;

class Autoloader {
	public function __invoke( $className ) {
		$namespaceLen = strlen( __NAMESPACE__ );
		if ( substr( $className, 0, $namespaceLen ) !== __NAMESPACE__ ) {
			return;
		}

		/** @noinspection PhpIncludeInspection */
		include_once __DIR__ . str_replace( '\\', DIRECTORY_SEPARATOR, substr( $className, $namespaceLen ) . '.php' );
	}
}
