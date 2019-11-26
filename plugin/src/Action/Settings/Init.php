<?php


namespace IssetBV\VideoPublisher\Wordpress\Action\Settings;


use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;
use IssetBV\VideoPublisher\Wordpress\Action\SetFeaturedImage;
use Timber\Timber;

class Init extends BaseAction {
	public function isAdminOnly() {
		return true;
	}

	function getAction() {
		return 'admin_init';
	}


	function execute( $arguments ) {
		$this->plugin->action(Scripts::class);
		$this->plugin->action(SetFeaturedImage::class);

		register_setting(
			'video_publisher_settings', // Option group
			'isset-video-publisher-options', // Option name
			function ( $input ) {
				$new_input = [];
				if ( isset( $input['consumer_key'] ) ) {
					$new_input['consumer_key'] = sanitize_text_field( $input['consumer_key'] );
				}

				if ( isset( $input['private_key'] ) ) {
					$new_input['private_key'] = sanitize_text_field( $input['private_key'] );
				}

				return $new_input;
			}
		);

		add_settings_section(
			'setting_section_id', // ID
			__( 'Video Publisher Settings', 'isset-video-publisher' ), // Title
			function () {
				_e( 'Enter your settings below:', 'isset-video-publisher' );
			},
			'publisher-admin' // Page
		);

		add_settings_field(
			'consumer_key', // ID
			'Consumer key', // Title
			function () {
				$issetVideoPublisher = $this->plugin->getVideoPublisherService();
				$this->renderInput( "consumer_key", $issetVideoPublisher->getConsumerKey() );
			},
			'publisher-admin', // Page
			'setting_section_id' // Section
		);

		add_settings_field(
			'private_key',
			'Private key',
			function () {
				$issetVideoPublisher = $this->plugin->getVideoPublisherService();
				$this->renderInput( "private_key", $issetVideoPublisher->getPrivateKey() );
			},
			'publisher-admin',
			'setting_section_id'
		);
	}

	private function renderInput( $name, $value ) {

		Timber::render( __DIR__ . '/../../../views/admin/input.html.twig', [
			'name'  => $name,
			'value' => $value
		] );
	}
}