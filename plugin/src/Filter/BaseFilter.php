<?php


namespace IssetBV\VideoPublisher\Wordpress\Filter;


use IssetBV\VideoPublisher\Wordpress\Plugin;

abstract class BaseFilter {
	/** @var Plugin */
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	abstract public function getFilters(): FilterCollection;

	public function register() {
		$this->getFilters()->register();
	}
}