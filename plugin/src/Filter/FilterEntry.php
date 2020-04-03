<?php


namespace IssetBV\VideoPublisher\Wordpress\Filter;


class FilterEntry {
	private $name;
	private $callable;
	private $args = 1;
	private $priority = 10;

	/**
	 * FilterEntry constructor.
	 *
	 * @param $name
	 * @param $callable
	 * @param int $args
	 * @param int $priority
	 */
	public function __construct( $name, $callable, int $args, int $priority ) {
		$this->name     = $name;
		$this->callable = $callable;
		$this->args     = $args;
		$this->priority = $priority;
	}

	public function register() {
		add_filter( $this->name, $this->callable, $this->priority, $this->args );
	}
}