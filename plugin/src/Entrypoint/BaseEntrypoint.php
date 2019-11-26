<?php


namespace IssetBV\VideoPublisher\Wordpress\Entrypoint;


use IssetBV\VideoPublisher\Wordpress\Plugin;

abstract class BaseEntrypoint {
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	abstract function enter();
}