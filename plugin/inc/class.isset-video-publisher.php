<?php

/**
 * Class IssetVideoPublisher.
 */
class IssetVideoPublisher
{
    /**
     *
     */
    const PUBLISHER_TOKEN_KEY = 'isset_video_publisher_token';
    /**
     *
     */
    const PUBLISHER_URL = 'https://my.videopublisher.io';

    /**
     * @var IssetVideoPublisher
     */
    private static $instance;

    /**
     * @var
     */
    private $issetVideoPublisherOptions;

    /**
     * @return IssetVideoPublisher
     */
    public static function instance()
    {
        if ( ! self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * IssetVideoPublisher constructor.
     */
    public function __construct()
    {
        $this->initPostType();
        add_shortcode('publish', [&$this, 'publish_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        load_plugin_textdomain('isset-video-publisher', false, 'isset-video-publisher/languages');

        add_action('rest_api_init', [$this, 'restApiInit']);
    }

    /**
     *
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('isset-video-publisher', ISSET_VIDEO_PUBLISHER_URL . 'isset-video-publisher.css', [], ISSET_VIDEO_PUBLISHER_VERSION);
        wp_enqueue_script('isset-video-publisher', ISSET_VIDEO_PUBLISHER_URL . '/isset-video-publisher.min.js', [], ISSET_VIDEO_PUBLISHER_VERSION, true);
    }

    /**
     * @return string
     */
    public function getPostType()
    {
        return 'video-publisher';
    }

    /**
     *
     */
    private function initOptions()
    {
        $this->issetVideoPublisherOptions = get_option('isset-video-publisher-options');
    }

    /**
     * @return string
     */
    public function getConsumerKey()
    {
        if (null === $this->issetVideoPublisherOptions) {
            $this->initOptions();
        }

        if (isset($this->issetVideoPublisherOptions['consumer_key'])) {
            return $this->issetVideoPublisherOptions['consumer_key'];
        }

        return '';
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        if (null === $this->issetVideoPublisherOptions) {
            $this->initOptions();
        }

        if (isset($this->issetVideoPublisherOptions['private_key'])) {
            return $this->issetVideoPublisherOptions['private_key'];
        }

        return '';
    }

    /**
     * @return bool
     */
    public function getPublishedVideos()
    {
        $this->getToken();

        $xauth_token = get_option(self::PUBLISHER_TOKEN_KEY);
        $response = wp_remote_get(
            sprintf(self::PUBLISHER_URL . '/api/published'),
            [
                'headers' => [
                    'xauth-token' => $xauth_token,
                ],
            ]
        );

        if (is_wp_error($response)) {
            return false;
        }

        if (wp_remote_retrieve_response_code($response) == 403) {
            update_option(self::PUBLISHER_TOKEN_KEY, false);
            $this->getToken();

            $xauth_token = get_option(self::PUBLISHER_TOKEN_KEY);
            if ($xauth_token) {
                return $this->getPublishedVideos();
            }
        }

        if (wp_remote_retrieve_response_code($response) == 200) {
            $publishes = json_decode($response['body'], true);

            if (is_array($publishes)) {
                foreach ($publishes as $publish) {
                    if (empty($publish['uuid'])) {
                        continue;
                    }

                    if ($publish['status'] === 'online' && (int)$publish['enabled'] === 1) {
                        $postStatus = 'publish';
                    } else {
                        $postStatus = 'draft';
                    }

                    $args = [
                        'post_type' => $this->getPostType(),
                        'name' => $publish['uuid'],
                        'post_status' => ['publish', 'draft'],
                    ];
                    $WP_Query = new WP_Query($args);

                    $postData = [
                        'post_name' => $publish['uuid'],
                        'post_type' => $this->getPostType(),
                        'post_status' => $postStatus,
                    ];

                    if ($WP_Query->post_count === 0) {
                        $postData['post_title'] = $publish['streamName'];

                        $postId = wp_insert_post($postData, true);
                        $post = get_post($postId);
                    } else {
                        $post = $WP_Query->next_post();

                        $postData['ID'] = $post->ID;
                        wp_update_post($postData);
                    }

                    update_post_meta($post->ID, 'video-publish', $publish);
                }
            }
        }

        return false;
    }

    /**
     * @param $uuid
     *
     * @return bool|string
     */
    private function getVideoUrl($uuid)
    {
        $xauth_token = get_option(self::PUBLISHER_TOKEN_KEY);
        $response = wp_remote_get(
            sprintf(self::PUBLISHER_URL . '/api/publish/%s', $uuid),
            [
                'headers' => [
                    'xauth-token' => $xauth_token,
                ],
            ]
        );

        if (is_wp_error($response)) {
            return false;
        }

        if (wp_remote_retrieve_response_code($response) == 403) {
            update_option(self::PUBLISHER_TOKEN_KEY, false);
            $this->getToken();

            $xauth_token = get_option(self::PUBLISHER_TOKEN_KEY);
            if ($xauth_token) {
                return $this->getVideoUrl($uuid);
            }
        }

        if (wp_remote_retrieve_response_code($response) == 200) {
            $body = json_decode($response['body']);
            if ($body->viewable) {
                return $body->view->playout_url;
            }
        }

        return false;
    }

    /**
     *
     */
    private function getToken()
    {
        $time = time();
        $response = wp_remote_post(
            self::PUBLISHER_URL . '/api/login',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode(
                    [
                        'consumer_key' => $this->getConsumerKey(),
                        'time' => $time,
                        'hash' => crypt($time . '' . $this->getPrivateKey() . '' . $this->getConsumerKey(), '$6$rounds=9001$' . $this->getConsumerKey() . '$'),
                    ]
                ),
            ]
        );

        if (wp_remote_retrieve_response_code($response) == 200) {
            $body = $response['body'];
            $data = json_decode($body);
            update_option(self::PUBLISHER_TOKEN_KEY, $data->token);
        }
    }

    /**
     * @param $params
     * @param null $content
     *
     * @return string
     */
    public function publish_shortcode($params, $content = null)
    {
        extract(
            shortcode_atts(
                [
                    'uuid' => false,
                    'post_id' => false,
                    'poster' => false,
                    'controls' => 'controls',
                    'autoplay' => '',
                    'loop' => 'loop',
                    'muted' => '',
                ],
                $params
            )
        );

        /* @var int $post_id */

        if ($uuid === false && $post_id === false) {
            return false;
        }

        $args = [];
        if ($uuid) {
            $args['post_name__in'] = [$uuid];
        }
        $args['p'] = $post_id;
        $args['post_type'] = $this->getPostType();
        $query = new WP_Query($args);
        if ((int)$query->found_posts !== 1) {
            return $content;
        }

        global $post;
        $query->the_post();

        $uuid = $post->post_name;
        $image = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'large');
        if (is_array($image)) {
            list($poster, $w, $h) = $image;
        }

        $video_url = $this->getVideoUrl($uuid);
        if ($video_url) {
            $result = sprintf('<div class="video-publisher-video video-publisher-video-16by9">' .
                '<video %s %s %s %s poster="%s" src="%s" playsinline class="video-js vjs-default-skin vjs-big-play-centered" data-setup=\'{"fluid": true}\'> ' .
                '%s' .
                '</video>' .
                '</div>', esc_attr($controls), esc_attr($autoplay), esc_attr($loop), esc_attr($muted), esc_url($poster), esc_url($video_url), __('Your browser does not support the video tag.', 'isset-video-publisher'));
        } else {
            $result = __('Video canot be loaded', 'isset-video-publisher');
        }

        wp_reset_query();

        return $result;
    }

    /**
     *
     */
    public function initPostType()
    {
        $labels = [
            'name' => _x('Videos', 'post type general name', 'isset-video-publisher'),
            'singular_name' => _x('Video', 'post type singular name', 'isset-video-publisher'),
            'menu_name' => _x('Videos', 'admin menu', 'isset-video-publisher'),
            'name_admin_bar' => _x('Video', 'add new on admin bar', 'isset-video-publisher'),
            'add_new' => _x('Import from publisher', 'video', 'isset-video-publisher'),
            'add_new_item' => __('Add New Video', 'isset-video-publisher'),
            'new_item' => __('Import from publisher', 'isset-video-publisher'),
            'edit_item' => __('Edit Video', 'isset-video-publisher'),
            'view_item' => __('View Video', 'isset-video-publisher'),
            'all_items' => __('All Videos', 'isset-video-publisher'),
            'search_items' => __('Search Videos', 'isset-video-publisher'),
            'parent_item_colon' => __('Parent Videos:', 'isset-video-publisher'),
            'not_found' => __('No videos found.', 'isset-video-publisher'),
            'not_found_in_trash' => __('No videos found in Trash.', 'isset-video-publisher'),
        ];

        $args = [
            'labels' => $labels,
            'description' => __('Description.', 'isset-video-publisher'),
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'video-publisher'],
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 30,
            'menu_icon' => 'dashicons-video-alt',
            'supports' => ['title', 'thumbnail'],
        ];

        register_post_type($this->getPostType(), $args);
    }

    /**
     * @return mixed|void
     */
    public function getFrontPageId()
    {
        return (int)get_option('isset-video-publisher-frontpage-id');
    }

    /**
     * @param $pageId
     *
     * @return bool
     */
    public function setFrontPageId($pageId)
    {
        return update_option('isset-video-publisher-frontpage-id', $pageId);
    }

    public function restApiInit()
    {
        register_rest_route('video-publisher/v1', '/update-publisher', [
            'methods' => 'GET',
            'callback' => [$this, 'updatePublisher'],
            'permission_callback' => function () {
                return current_user_can('edit_others_posts');
            },
        ]);
    }

    public function updatePublisher()
    {
        $this->getPublishedVideos();

        return [
            'message' => 'videos updated'
        ];
    }
}
