<?php


namespace IssetBV\VideoPublisher\Wordpress\Internal;

class Action {
	private $controller;
	private $method;
	private $permissions;

	/**
	 * Action constructor.
	 *
	 * @param $controller
	 * @param $method
	 * @param $permissions
	 */
	public function __construct( $controller, $method, $permissions ) {
		$this->controller  = $controller;
		$this->method      = $method;
		$this->permissions = $permissions;
	}


	public function __call( $name, $arguments ) {
		if ( ! method_exists( $this->controller, $name ) ) {
			throw new \RuntimeException( "Function $name doesn't exist on " . get_class( $this->controller ) );
		}

		return array(
			'methods'             => $this->method,
			'callback'            => array( $this->controller, $name ),
			'permission_callback' => function () {
				foreach ( $this->permissions as $permission ) {
					if ( ! current_user_can( $permission ) ) {
						return false;
					}
				}

				return true;
			},
		);
	}
}
