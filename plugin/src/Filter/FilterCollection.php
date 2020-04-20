<?php


namespace IssetBV\VideoPublisher\Wordpress\Filter;


class FilterCollection {
	/**
	 * @var FilterEntry[]
	 */
	private $filters = [];

	public static function new() {
		return new FilterCollection();
	}

	/**
	 * @param string $name
	 * @param callable $callable
	 * @param int $priority
	 * @param int $numArgs
	 *
	 * @return $this
	 */
	public function add( $name, $callable, $priority = 10, $numArgs = 1 ) {
		return $this->addEntry( new FilterEntry( $name, $callable, $priority, $numArgs ) );
	}

	/**
	 * @param FilterEntry $entry
	 *
	 * @return $this
	 */
	public function addEntry( $entry ) {
		$this->filters[] = $entry;

		return $this;
	}

	public function register() {
		foreach ( $this->filters as $filter ) {
			$filter->register();
		}
	}
}