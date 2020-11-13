<?php


namespace IssetBV\VideoPublisher\Wordpress\Widgets;

abstract class BaseWidget {
	abstract public function getWidgetId();

	abstract public function getWidgetName();

	abstract public function execute( $args );

	public function controlCallback() {
		 return null;
	}

	public function getArgs() {
		 return 1;
	}

	public function __invoke( ...$args ) {
		$this->execute( $args );
	}
}
