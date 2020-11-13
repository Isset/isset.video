<?php


namespace IssetBV\VideoPublisher\Wordpress\Shortcode;

use IssetBV\VideoPublisher\Wordpress\Plugin;

abstract class ShortcodeBase {
	const CODE = 'noop';
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	function getCode() {
		return static::CODE;
	}

	abstract function generate( $params, $content = null );

	public function __invoke( $params, $content = null ) {
		return $this->generate( $params, $content );
	}
}
