<?php


namespace IssetBV\VideoPublisher\Wordpress;


use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;
use IssetBV\VideoPublisher\Wordpress\Action\Editor;
use IssetBV\VideoPublisher\Wordpress\Action\HijackRouter;
use IssetBV\VideoPublisher\Wordpress\Action\ImportPublishedVideos;
use IssetBV\VideoPublisher\Wordpress\Action\SavePost;
use IssetBV\VideoPublisher\Wordpress\Action\Settings;
use IssetBV\VideoPublisher\Wordpress\Action\ThumbnailColumn;
use IssetBV\VideoPublisher\Wordpress\Action\Upload;
use IssetBV\VideoPublisher\Wordpress\Filter\BaseFilter;
use IssetBV\VideoPublisher\Wordpress\Filter\ThumbnailColumnFilter;
use IssetBV\VideoPublisher\Wordpress\Filter\Timber;
use IssetBV\VideoPublisher\Wordpress\Filter\VideoUpload;
use IssetBV\VideoPublisher\Wordpress\MetaBox\BaseMetaBox;
use IssetBV\VideoPublisher\Wordpress\MetaBox\FrontPage;
use IssetBV\VideoPublisher\Wordpress\MetaBox\ThumbnailSelect;
use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use IssetBV\VideoPublisher\Wordpress\Rest\PublishesEndpoint;
use IssetBV\VideoPublisher\Wordpress\Service\ThumbnailService;
use IssetBV\VideoPublisher\Wordpress\Service\VideoPublisherService;
use IssetBV\VideoPublisher\Wordpress\Service\WordpressService;
use IssetBV\VideoPublisher\Wordpress\Shortcode\Publish;
use IssetBV\VideoPublisher\Wordpress\Shortcode\ShortcodeBase;

class Plugin {
	static $instance;

	/**
	 * @var VideoPublisherService
	 */
	private $videoPublisherService;

	/**
	 * @var ThumbnailService
	 */
	private $thumbnailService;

	/**
	 * @var WordpressService
	 */
	private $wordpressService;

	private $shortcodes = [
		Publish::class,
	];

	private $metaBoxes = [
		FrontPage::class,
		ThumbnailSelect::class,
	];

	private $actions = [
		HijackRouter::class,
		SavePost::class,
		ImportPublishedVideos::class,
		Settings\Init::class,
		Settings\Menu::class,
		ThumbnailColumn::class,
		Upload\GenerateUploadUrl::class,
		Upload\RegisterUpload::class,
		Editor::class,
	];

	private $filters = [
		ThumbnailColumnFilter::class,
		VideoUpload::class,
		Timber::class,
	];

	private $scripts = [
		'js/main.js' => [ 'site', 'admin' ],
	];

	private $styles = [
		'css/main.css' => [ 'site', 'admin' ],
	];


	const PUBLISHER_URL = 'https://my.videopublisher.io/';
	const MY_ISSET_VIDEO_URL = 'https://my.isset.video/';

	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	public function init() {
		$this->initPostTypes();
		$this->addShortcodes();
		$this->initScripts();
		$this->loadTranslations();
		$this->initActions();
		$this->initRest();
		$this->initBlocks();
		$this->initRecurring();
		$this->registerActivationHooks();
		$this->initFilters();

		if ( is_admin() ) {
			$this->initMetaBoxes();
		}
	}

	private function addShortcodes() {
		foreach ( $this->shortcodes as $shortcode ) {
			/** @var ShortcodeBase $shortcodeObj */
			$shortcodeObj = new $shortcode( $this );
			add_shortcode( $shortcodeObj->getCode(), $shortcodeObj );
		}
	}

	public function enqueueScript( $script ) {
		wp_enqueue_script(
			'isset-video-publisher-' . $script,
			ISSET_VIDEO_PUBLISHER_URL . '/' . $script,
			[],
			ISSET_VIDEO_PUBLISHER_VERSION . '-' . filemtime( ISSET_VIDEO_PUBLISHER_PATH . '/' . $script ),
			true
		);
	}

	private function enqueueScripts( $context ) {
		foreach ( $this->scripts as $script => $applicableContexts ) {
			if ( in_array( $context, $applicableContexts, true ) ) {
				$this->enqueueScript( $script );
			}
		}

		foreach ( $this->styles as $style => $applicableContexts ) {
			if ( in_array( $context, $applicableContexts, true ) ) {
				$this->enqueueStyle( $style );
			}
		}
	}

	private function initScripts() {
		add_action( 'admin_enqueue_scripts', function () {
			$this->enqueueScripts( 'admin' );
		} );

		add_action( 'wp_enqueue_scripts', function () {
			$this->enqueueScripts( 'site' );
		} );
	}

	private function initPostTypes() {
		register_post_type( VideoPublisher::getTypeName(), VideoPublisher::getArgs() );
	}

	private function loadTranslations() {
		load_plugin_textdomain( 'isset-video-publisher', false, 'isset-video-publisher/languages' );
	}

	private function initActions() {
		foreach ( $this->actions as $action ) {
			$this->action( $action );
		}
	}

	private function initFilters() {
		foreach ( $this->filters as $filter ) {
			$this->filter( $filter );
		}
	}

	/**
	 * @return VideoPublisherService
	 */
	public function getVideoPublisherService() {
		if ( $this->videoPublisherService === null ) {
			$this->videoPublisherService = new VideoPublisherService( $this );
		}

		return $this->videoPublisherService;
	}

	/**
	 * @return ThumbnailService
	 */
	public function getThumbnailService() {
		if ( $this->thumbnailService === null ) {
			$this->thumbnailService = new ThumbnailService( $this );
		}

		return $this->thumbnailService;
	}

	private function initMetaBoxes() {
		add_action( 'add_meta_boxes', function () {
			foreach ( $this->metaBoxes as $metaBox ) {
				/** @var BaseMetaBox $metaBoxObj */
				$metaBoxObj = new $metaBox( $this );
				if ( $metaBoxObj->isVisible() ) {
					add_meta_box( $metaBoxObj->getId(), $metaBoxObj->getTitle(), $metaBoxObj, $metaBoxObj->getScreen(), $metaBoxObj->getContext() );
				}
			}
		} );
	}

	public function getFrontPageId() {
		return get_option( 'isset-video-publisher-frontpage-id' );
	}

	public function setFrontPageId( $id ) {
		return update_option( 'isset-video-publisher-frontpage-id', $id, true );
	}

	/**
	 * @param $class
	 *
	 * @return BaseEntrypoint
	 */
	public function entrypoint( $class ) {
		return new $class( $this );
	}

	public function action( $action ) {
		/** @var BaseAction $actionObj */
		$actionObj = new $action( $this );
		if ( $actionObj->isAdminOnly() && ! is_admin() ) {
			return;
		}

		add_action( $actionObj->getAction(), $actionObj, $actionObj->getPriority(), $actionObj->getArgs() );
	}

	public function initRest() {
		PublishesEndpoint::publishes();
	}

	private function initBlocks() {
		$this->registerBlock( 'video-block' );
	}

	private function registerBlock( $name ) {
		wp_register_script(
			"isset-video-publisher-{$name}",
			plugins_url( "../js/publisher-{$name}.js", __FILE__ ),
			[ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components' ]
		);

		wp_register_style(
			"isset-video-publisher-{$name}-style",
			plugins_url( '../css/main.css', __FILE__ ),
			[ 'wp-edit-blocks' ]
		);

		register_block_type( "isset-video-publisher/{$name}", [
			'editor_script' => "isset-video-publisher-{$name}",
			'editor_style'  => "isset-video-publisher-{$name}-style"
		] );
	}

	private function initRecurring() {
		if ( ! wp_next_scheduled( 'fetch_publishes' ) ) {
			wp_schedule_event( time(), 'hourly', 'fetch_publishes' );
		}
	}

	private function registerActivationHooks() {
		add_action( 'admin_init', function () {
			if ( is_admin() && current_user_can( 'activate_plugins' ) && ! is_plugin_active( 'timber-library/timber.php' ) ) {
				add_action( 'admin_notices', function () {
					?>
                    <div class="error"><p>Isset video publisher plugin requires <a
                                    href="https://wordpress.org/plugins/timber-library/" target="_blank">timber</a> to
                            work, please install and activate the timber plugin.</p></div><?php
				} );

				deactivate_plugins( plugin_basename( __FILE__ ) );

				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		} );
	}

	public function filter( $filter ) {
		/** @var BaseFilter $filterObj */
		$filterObj = new $filter( $this );
		$filterObj->register();
	}

	public function enqueueStyle( $style ) {
		wp_enqueue_style(
			'isset-video-publisher-' . $style,
			ISSET_VIDEO_PUBLISHER_URL . '/' . $style,
			[],
			ISSET_VIDEO_PUBLISHER_VERSION . '-' . filemtime( ISSET_VIDEO_PUBLISHER_PATH . '/' . $style )
		);
	}

	public function getWordpressService() {
		if ( $this->wordpressService === null ) {
			$this->wordpressService = new WordpressService( $this );
		}

		return $this->wordpressService;
	}
}
