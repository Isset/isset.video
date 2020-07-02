<?php


namespace IssetBV\VideoPublisher\Wordpress\Action;


class RecurringSync extends BaseAction {
	function execute( $arguments ) {
		error_log( 'Update publishes' );
		do_action( 'isset-video-sync' );
	}

	function getAction() {
		return 'isset-video-publisher-recurring-sync';
	}

	public function getPriority() {
		return 1;
	}
}