<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Settings;

use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;
use IssetBV\VideoPublisher\Wordpress\Renderer;

class Init extends BaseAction {
	public function isAdminOnly() {
		return true;
	}

	function getAction() {
		return 'admin_init';
	}

	function execute( $arguments ) {
		$this->plugin->action( Scripts::class );
	}
}
