<?php


namespace IssetBV\VideoPublisher\Wordpress;

use IssetBV\VideoPublisher\Wordpress\Action\BaseAction;
use IssetBV\VideoPublisher\Wordpress\Action\HijackRouter;
use IssetBV\VideoPublisher\Wordpress\Action\Settings;
use IssetBV\VideoPublisher\Wordpress\Action\Upload;
use IssetBV\VideoPublisher\Wordpress\Action\Upload\CreateArchiveFile;
use IssetBV\VideoPublisher\Wordpress\Rest\BaseEndpoint;
use IssetBV\VideoPublisher\Wordpress\Service\VideoArchiveService;
use IssetBV\VideoPublisher\Wordpress\Service\VideoPublisherService;
use IssetBV\VideoPublisher\Wordpress\Shortcode\Publish;
use IssetBV\VideoPublisher\Wordpress\Shortcode\ShortcodeBase;
use IssetBV\VideoPublisher\Wordpress\Widgets\BaseWidget;
use IssetBV\VideoPublisher\Wordpress\Widgets\Dashboard;

class Plugin {
    const MENU_MAIN_SLUG = 'isset-video-overview';
    const MENU_UPLOAD_SLUG = 'isset-video-upload';

    static $instance;

	/**
	 * @var VideoPublisherService
	 */
	private $videoPublisherService;

    /**
     * @var VideoArchiveService
     */
    private $videoArchiveService;

	private $shortcodes = [
		Publish::class,
	];

	private $actions = [
		HijackRouter::class,
        Settings\Init::class,
		Settings\Menu::class,
		Upload\GenerateUploadUrl::class,
        Upload\GetArchiveToken::class,
        Upload\GetArchiveUrl::class,
        Upload\GetUploaderUrl::class,
        CreateArchiveFile::class,
	];

	private $endpoints = [
	];

	private $scripts = [
		'js/main.js' => [ 'site', 'admin' ],
	];

	private $styles = [
		'css/main.css' => [ 'site', 'admin' ],
	];

	private $dashboardWidgets = [
		Dashboard::class
	];

	private $helpers = [
	    'Statistics',
    ];

	const PUBLISHER_URL = 'https://test.publish.isset.video/';
	const MY_ISSET_VIDEO_URL = 'https://test.my.isset.video/';
	const ARCHIVE_URL = 'https://test.archive.isset.video/';
	const UPLOADER_BASE_URL = 'https://test.upload.isset.video/';

	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	public function init() {
        $this->initSession();
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

	private function loadTranslations() {
		load_plugin_textdomain( 'isset-video-publisher', false, 'isset-video-publisher/languages' );
	}

	private function initActions() {
		foreach ( $this->actions as $action ) {
			$this->action( $action );
		}
	}

	private function loadHelpers() {
        foreach ( $this->helpers as $helper ) {
            include_once(ISSET_VIDEO_PUBLISHER_PATH . 'src/Helpers/' . $helper . '.php');
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
		add_action( 'rest_api_init', function () {
			foreach ( $this->endpoints as $endpoint ) {
				/** @var BaseEndpoint $endpointObj */
				$endpointObj = $this->endpoint( $endpoint );
				register_rest_route( 'isset-publisher/v1', $endpointObj->getRoute(), [
					'methods'  => $endpointObj->getMethod(),
					'callback' => $endpointObj,
                    'permission_callback' => function () {
				        return current_user_can( 'edit_posts' );
                    }
				] );
			}
		} );
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

	private function initDashboardWidgets() {
		add_action( 'wp_dashboard_setup', function () {
			foreach ( $this->dashboardWidgets as $widget ) {
				$this->dashboardWidget( $widget );
			}
		} );
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
        $this->addNewVideoItem();
    }

    public function getOverviewPageUrl() {
	    return admin_url( 'admin.php?page=' . self::MENU_MAIN_SLUG );
    }

    public function getUploadUrl() {
	    return admin_url( 'admin.php?page=' . self::MENU_UPLOAD_SLUG );
    }

    public function renderOverviewPage() {
        $vps     = $this->getVideoPublisherService();
        $context = [];
        $context['logged_in'] = $vps->isLoggedIn();

        if ( $context['logged_in'] ) {
            $context['uploadUrl'] = $this->getUploadUrl();
            $context['chart'] = $this->renderChart();

            echo Renderer::render( 'admin/overview.php', $context );
        } else {
            $context['login_url'] = $vps->getLoginURL();

            echo Renderer::render( 'admin/page.php', $context );
        }
	}

    public function renderUploadPage() {
        $service = new VideoPublisherService($this);

        $data = [];
        $data['logged_in'] = $service->isLoggedIn();
        $data['uploading_allowed'] = $service->uploadingAllowed();
        $data['video_url'] = $this->getOverviewPageUrl();

        if ( $data['logged_in'] ) {
            echo Renderer::render('admin/upload.php', $data);
        } else {
            $data['login_url'] = $service->getLoginURL();

            echo Renderer::render( 'admin/page.php', $data );
        }
    }

    private function addOverviewItem()
    {
        $page_title = 'Isset Videos';
        $menu_title = 'Videos';
        $capability = 'manage_options';
        $menu_slug  = self::MENU_MAIN_SLUG;
        $function   = function() { $this->renderOverviewPage(); };
        $icon_url   = 'dashicons-video-alt';
        $position   = 11;

        add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
    }

    private function addNewVideoItem()
    {
        $this->enqueueScript( 'js/admin-video-upload.js' );
        $this->enqueueStyle( 'css/admin-video-upload.css' );

        $page_title = 'Upload New Videos';
        $menu_title = 'New Video';
        $capability = 'manage_options';
        $parent_slug = self::MENU_MAIN_SLUG;
        $function   = function() { $this->renderUploadPage(); };

        add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, self::MENU_UPLOAD_SLUG, $function );
    }

    private function renderChart()
    {
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
}
