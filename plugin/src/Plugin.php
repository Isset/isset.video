<?php


namespace IssetBV\VideoPublisher\Wordpress;

use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;
use IssetBV\VideoPublisher\Wordpress\Action\HijackRouter;
use IssetBV\VideoPublisher\Wordpress\Action\Settings;
use IssetBV\VideoPublisher\Wordpress\Action\Upload;
use IssetBV\VideoPublisher\Wordpress\Rest\BaseEndpoint;
use IssetBV\VideoPublisher\Wordpress\Service\VideoArchiveService;
use IssetBV\VideoPublisher\Wordpress\Service\VideoPublisherService;
use IssetBV\VideoPublisher\Wordpress\Shortcode\Livestream;
use IssetBV\VideoPublisher\Wordpress\Shortcode\Publish;
use IssetBV\VideoPublisher\Wordpress\Shortcode\ShortcodeBase;
use IssetBV\VideoPublisher\Wordpress\Widgets\BaseWidget;
use IssetBV\VideoPublisher\Wordpress\Widgets\Dashboard;

class Plugin {
	const MENU_MAIN_SLUG       = 'isset-video-overview';
	const MENU_LIVESTREAM_SLUG = 'isset-video-livestream';

	static $instance;

	/**
	 * @var VideoPublisherService
	 */
	private $videoPublisherService;

	/**
	 * @var VideoArchiveService
	 */
	private $videoArchiveService;

	/**
	 * @var string[]
	 */
	private $shortcodes = array(
		Publish::class,
		Livestream::class,
	);

	/**
	 * @var string[]
	 */
	private $config = array();

	private $actions = array(
		HijackRouter::class,
		Settings\Init::class,
		Upload\GenerateUploadUrl::class,
		Upload\GetArchiveToken::class,
		Upload\GetArchiveUrl::class,
		Upload\GetUploaderUrl::class,
		Upload\CreateArchiveFile::class,
		Upload\GetUploadAllowed::class,
		Action\Livestream\StreamDetails::class,
	);

	private $endpoints = array();

	private $scripts = array(
		'js/main.js' => array( 'site' ),
	);

	private $styles = array(
		'css/main.css' => array( 'site', 'admin' ),
	);

	private $dashboardWidgets = array(
		Dashboard::class,
	);

	private $helpers = array(
		'Statistics',
	);

	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	public function init() {
		$this->initSession();
		$this->initConfig();
		$this->addShortcodes();
		$this->initScripts();
		$this->loadTranslations();
		$this->loadHelpers();
		$this->initActions();
		$this->initRest();
		$this->initBlocks();

		if ( is_admin() ) {
			$this->initDashboardWidgets();
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
			ISSET_VIDEO_PUBLISHER_URL . $script,
			array('wp-i18n'),
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
		add_action(
			'admin_enqueue_scripts',
			function () {
				$this->enqueueScripts( 'admin' );
			}
		);

		add_action(
			'wp_enqueue_scripts',
			function () {
				$this->enqueueScripts( 'site' );
			}
		);
	}

	private function loadTranslations() {
		load_plugin_textdomain( 'isset-video', false, 'isset-video/languages' );
	}

	private function initActions() {
		foreach ( $this->actions as $action ) {
			$this->action( $action );
		}
	}

	private function loadHelpers() {
		foreach ( $this->helpers as $helper ) {
			include_once ISSET_VIDEO_PUBLISHER_PATH . 'src/Helpers/' . $helper . '.php';
		}
	}

	/**
	 * @return VideoPublisherService
	 */
	public function getVideoPublisherService() {
		if ( $this->videoPublisherService === null ) {
			$this->videoPublisherService = new VideoPublisherService( $this, $this->config );
		}

		return $this->videoPublisherService;
	}

	/**
	 * @return VideoArchiveService
	 */
	public function getVideoArchiveService() {
		if ( $this->videoArchiveService === null ) {
			$this->videoArchiveService = new VideoArchiveService( $this );
		}

		return $this->videoArchiveService;
	}

	public function getFrontPageId() {
		return get_option( 'isset-video-publisher-frontpage-id' );
	}

	public function setFrontPageId( $id ) {
		return update_option( 'isset-video-publisher-frontpage-id', $id, true );
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
		add_action(
			'rest_api_init',
			function () {
				foreach ( $this->endpoints as $endpoint ) {
					/** @var BaseEndpoint $endpointObj */
					$endpointObj = $this->endpoint( $endpoint );
					register_rest_route(
						'isset-publisher/v1',
						$endpointObj->getRoute(),
						array(
							'methods'             => $endpointObj->getMethod(),
							'callback'            => $endpointObj,
							'permission_callback' => function () {
								return current_user_can( 'edit_posts' );
							},
						)
					);
				}
			}
		);
	}

	private function initBlocks() {
		$this->registerBlock( 'video-block' );
		$this->registerBlock( 'livestream-block' );
	}

	private function registerBlock( $name ) {
		wp_register_script(
			"isset-video-publisher-{$name}",
			plugins_url( "../js/publisher-{$name}.js", __FILE__ ),
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' )
		);

		wp_register_style(
			"isset-video-publisher-{$name}-style",
			plugins_url( '../css/main.css', __FILE__ ),
			array( 'wp-edit-blocks' )
		);

		register_block_type(
			"isset-video-publisher/{$name}",
			array(
				'editor_script' => "isset-video-publisher-{$name}",
				'editor_style'  => "isset-video-publisher-{$name}-style",
			)
		);
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
			array(),
			ISSET_VIDEO_PUBLISHER_VERSION . '-' . filemtime( ISSET_VIDEO_PUBLISHER_PATH . '/' . $style )
		);
	}

	private function initDashboardWidgets() {
		add_action(
			'wp_dashboard_setup',
			function () {
				foreach ( $this->dashboardWidgets as $widget ) {
					$this->dashboardWidget( $widget );
				}
			}
		);
	}

	public function dashboardWidget( $widget ) {
		/** @var BaseWidget $widgetObj */
		$widgetObj = new $widget( $this );

		wp_add_dashboard_widget( $widgetObj->getWidgetId(), $widgetObj->getWidgetName(), $widgetObj, $widgetObj->controlCallback(), $widgetObj->getArgs() );
	}

	public function endpoint( $endpoint ) {
		/** @var BaseEndpoint $endpointObj */
		return new $endpoint( $this );
	}

	private function initSession() {
		if ( ! session_id() ) {
			session_start();
		}
	}

	public function addMenuItems() {
		$this->addOverviewItem();
		$this->addLivestreamItem();
	}

	public function getOverviewPageUrl() {
		return admin_url( 'admin.php?page=' . self::MENU_MAIN_SLUG );
	}

	public function renderOverviewPage() {
		$vps                  = $this->getVideoPublisherService();
		$context              = array();
		$context['logged_in'] = $vps->isLoggedIn();

		if ( $context['logged_in'] ) {
			$context['chart'] = $this->renderChart();

			echo Renderer::render( 'admin/overview.php', $context );
		} else {
			$context['login_url'] = $vps->getLoginURL();

			echo Renderer::render( 'admin/page.php', $context );
		}
	}

	private function addOverviewItem() {
		$page_title = 'Isset Videos';
		$menu_title = __( 'Videos', 'isset-video' );
		$capability = 'manage_options';
		$menu_slug  = self::MENU_MAIN_SLUG;
		$function   = function() {
			$this->renderOverviewPage();
		};
		$icon_url   = 'dashicons-video-alt';
		$position   = 11;

		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	}

	public function renderLivestreamPage() {
		$vps                  = $this->getVideoPublisherService();
		$context              = array();
		$context['logged_in'] = $vps->isLoggedIn();

		if ( $context['logged_in'] ) {
			echo Renderer::render( 'admin/livestreams.php', $context );
		} else {
			$context['login_url'] = $vps->getLoginURL();

			echo Renderer::render( 'admin/page.php', $context );
		}
	}

	private function addLivestreamItem() {
		//$this->enqueueStyle( 'css/main.css' );
		// $this->enqueueScript( 'js/admin-video-livestream.js' );

		$page_title  = __( 'Livestream', 'isset-video' );
		$menu_title  = __( 'Livestream', 'isset-video' );
		$capability  = 'manage_options';
		$parent_slug = self::MENU_MAIN_SLUG;
		$function    = function() {
			$this->renderLivestreamPage();
		};

		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, self::MENU_LIVESTREAM_SLUG, $function );
	}

	private function renderChart() {
		$service = $this->getVideoPublisherService();

		$context['isLoggedIn'] = $service->isLoggedIn();

		if ( $context['isLoggedIn'] ) {
			$userInfo = $service->getUserInfo();

			$context['user']               = $userInfo;
			$context['logout_url']         = $service->getLogoutURL();
			$context['videos_url']         = $this->getOverviewPageUrl();
			$context['stats']              = $service->fetchStats();
			$context['usage']              = $service->fetchUsage();
			$context['subscription_limit'] = $service->fetchSubscriptionLimit();
			$context['isset_video_url']    = $service->getMyIssetVideoURL();
		} else {
			$context['login_url'] = $service->getLoginURL();
		}

		return Renderer::render( 'admin/dashboard/api-dashboard.php', $context );
	}

	private function initConfig() {
		require_once ISSET_VIDEO_PUBLISHER_PATH . 'src/config.php';

		if ( file_exists( ISSET_VIDEO_PUBLISHER_PATH . 'src/config_override.php' ) ) {
			require_once ISSET_VIDEO_PUBLISHER_PATH . 'src/config_override.php';
		}

		$this->config = $isset_video_config;
	}
}
