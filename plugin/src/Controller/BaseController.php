<?php


namespace IssetBV\VideoPublisher\Wordpress\Controller;


use IssetBV\VideoPublisher\Wordpress\Internal\Action;
use IssetBV\VideoPublisher\Wordpress\Plugin;

abstract class BaseController {
	/** @var Plugin */
	protected $plugin;

	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * @param $method
	 * @param array $permissions
	 *
	 * @return Action|static
	 */
	public function route( $method, array $permissions = [] ) {
		return new Action( $this, $method, $permissions );
	}

	public function post( $permissions = [] ) {
		return $this->route( 'POST', $permissions );
	}

	/**
	 * @param array $permissions
	 *
	 * @return Action|static
	 */
	public function get( $permissions = [] ) {
		return $this->route( 'GET', $permissions );
	}
}