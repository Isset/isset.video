<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;

use IssetBV\VideoPublisher\Wordpress\Plugin;

abstract class BaseAction {
	/** @var Plugin */
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * @return mixed
	 */
	abstract function execute( $arguments );

	abstract function getAction();

	function isAdminOnly() {
		return false;
	}

	function isSiteOnly() {
		return false;
	}

	function getPriority() {
		return 10;
	}

	function getArgs() {
		return 1;
	}

	public function __invoke( ...$args ) {
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		return $this->execute( $args );
	}
}
