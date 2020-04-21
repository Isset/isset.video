<?php


namespace IssetBV\VideoPublisher\Wordpress\MetaBox;


use IssetBV\VideoPublisher\Wordpress\Plugin;

abstract class BaseMetaBox {
	/**
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * @param Plugin $plugin
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	abstract function getId();

	abstract function getTitle();

	abstract function render();

	function getScreen() {
		return null;
	}

	function getContext() {
		return 'advanced';
	}

	public function isVisible() {
		return true;
	}

	function __invoke() {
		$this->render();
	}
}