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
				$new_input = [];
				if ( isset( $input['show_advanced_options'] ) ) {
					$new_input['show_advanced_options'] = sanitize_text_field( $input['show_advanced_options'] ) === "1";
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
		$advanced_section = 'isset-video-publisher-advanced-section';

		add_settings_section(
			$default_section, // ID
			__( 'isset.video settings', 'isset-video-publisher' ), // Title
			function () {
				_e( 'Enter your settings below:', 'isset-video-publisher' );
			},
			'publisher-admin' // Page
		);

		add_settings_field(
			"show_advanced_options", // ID
			__( 'Show advanced options', 'isset-video-publisher' ), // Title
			function () {
				$issetVideoPublisher = $this->plugin->getVideoPublisherService();
				$this->renderInput( "show_advanced_options", "1", "checkbox", [
					'checked' => $issetVideoPublisher->shouldShowAdvancedOptions()
				] );
			},
			'publisher-admin', // Page
			$default_section // Section
		);

		if ( $vps->shouldShowAdvancedOptions() ) {
			add_settings_section(
				$advanced_section, // ID
				__( 'Advanced settings', 'isset-video-publisher' ), // Title
				function () {
					_e( 'Enter your settings below:', 'isset-video-publisher' );
				},
				'publisher-admin' // Page
			);


			add_settings_field(
				"my_isset_video_url", // ID
				'My isset video URL', // Name
				function () {
					$issetVideoPublisher = $this->plugin->getVideoPublisherService();
					$this->renderInput( "my_isset_video_url", $issetVideoPublisher->getMyIssetVideoURL() );
				},
				'publisher-admin', // Page
				$advanced_section // Section
			);

			add_settings_field(
				"publisher_url", // ID
				'Publisher URL', // Name
				function () {
					$issetVideoPublisher = $this->plugin->getVideoPublisherService();
					$this->renderInput( "publisher_url", $issetVideoPublisher->getPublisherURL() );
				},
				'publisher-admin', // Page
				$advanced_section // Section
			);
		}
	}

	private function renderInput( $name, $value, $type = 'text', $extra = [] ) {
        echo Renderer::render( 'admin/input.php', [
            'name'  => $name,
            'value' => $value,
            'type'  => $type,
            'extra' => $extra,
        ] );
	}
}