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
		$vps = $this->plugin->getVideoPublisherService();

		register_setting(
			'video_publisher_settings', // Option group
			'isset-video-publisher-options', // Option name
			function ( $input ) {
				$new_input = array();
				if ( isset( $input['show_advanced_options'] ) ) {
					$new_input['show_advanced_options'] = sanitize_text_field( $input['show_advanced_options'] ) === '1';
				}

				if ( isset( $input['my_isset_video_url'] ) ) {
					$new_input['my_isset_video_url'] = sanitize_text_field( $input['my_isset_video_url'] );
				}

				if ( isset( $input['publisher_url'] ) ) {
					$new_input['publisher_url'] = sanitize_text_field( $input['publisher_url'] );
				}

				return $new_input;
			}
		);

		$default_section  = 'isset-video-publisher-default-section';

		add_settings_section(
			$default_section, // ID
			__( 'isset.video settings', 'isset-video-publisher' ), // Title
			function () {
				_e( 'Enter your settings below:', 'isset-video-publisher' );
			},
			'publisher-admin' // Page
		);
	}
}
