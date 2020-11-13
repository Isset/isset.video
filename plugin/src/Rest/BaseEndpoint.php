<?php


namespace IssetBV\VideoPublisher\Wordpress\Rest;

use IssetBV\VideoPublisher\Wordpress\Plugin;

abstract class BaseEndpoint {
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	public function __invoke( $request ) {
		return $this->execute( $request );
	}

	abstract function getRoute();

	abstract function execute( $request );

	function getMethod() {
		return 'GET';
	}
}
