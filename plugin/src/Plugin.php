<?php


namespace IssetBV\VideoPublisher\Wordpress;


use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;
use IssetBV\VideoPublisher\Wordpress\Action\HijackRouter;
use IssetBV\VideoPublisher\Wordpress\Action\ImportPublishedVideos;
use IssetBV\VideoPublisher\Wordpress\Action\RestRouter;
use IssetBV\VideoPublisher\Wordpress\Action\SavePost;
use IssetBV\VideoPublisher\Wordpress\Action\Settings;
use IssetBV\VideoPublisher\Wordpress\Action\ThumbnailColumn;
use IssetBV\VideoPublisher\Wordpress\Entrypoint\BaseEntrypoint;
use IssetBV\VideoPublisher\Wordpress\Filter\BaseFilter;
use IssetBV\VideoPublisher\Wordpress\Filter\ThumbnailColumnFilter;
use IssetBV\VideoPublisher\Wordpress\MetaBox\BaseMetaBox;
use IssetBV\VideoPublisher\Wordpress\MetaBox\FrontPage;
use IssetBV\VideoPublisher\Wordpress\MetaBox\Preview;
use IssetBV\VideoPublisher\Wordpress\MetaBox\ThumbnailSelect;
use IssetBV\VideoPublisher\Wordpress\PostType\VideoPublisher;
use IssetBV\VideoPublisher\Wordpress\Service\ThumbnailService;
use IssetBV\VideoPublisher\Wordpress\Service\VideoPublisherService;
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

	private $shortcodes = [
		Publish::class,
	];

	private $metaBoxes = [
		Preview::class,
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
	];

	private $filters = [
		ThumbnailColumnFilter::class,
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

	private function enqueueScripts( $context ) {
		foreach ( $this->scripts as $script => $applicableContexts ) {
			if ( in_array( $context, $applicableContexts, true ) ) {
				wp_enqueue_script(
					'isset-video-publisher-' . $script,
					ISSET_VIDEO_PUBLISHER_URL . '/' . $script,
					[],
					ISSET_VIDEO_PUBLISHER_VERSION . '-' . filemtime( ISSET_VIDEO_PUBLISHER_PATH . '/' . $script ),
					true
				);
			}
		}

		foreach ( $this->styles as $style => $applicableContexts ) {
			if ( in_array( $context, $applicableContexts, true ) ) {
				wp_enqueue_style(
					'isset-video-publisher-' . $style,
					ISSET_VIDEO_PUBLISHER_URL . '/' . $style,
					[],
					ISSET_VIDEO_PUBLISHER_VERSION . '-' . filemtime( ISSET_VIDEO_PUBLISHER_PATH . '/' . $style )
				);
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
				add_meta_box( $metaBoxObj->getId(), $metaBoxObj->getTitle(), $metaBoxObj );
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

	public function filter( $filter ) {
		/** @var BaseFilter $filterObj */
		$filterObj = new $filter( $this );
		$filterObj->register();
	}

}