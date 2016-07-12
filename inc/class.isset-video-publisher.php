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
    const PUBLISHER_URL = 'http://test.my.videopublisher.io';

    /**
     * @var IssetVideoPublisher
     */
    private static $instance;

    /**
     * @var
     */
    private $issetVideoPublisherOptions;

    /**
     * IssetVideoPublisher constructor.
     */
    public function __construct()
    {
        add_shortcode('publish', [&$this, 'publish_shortcode']);
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

        if(isset($this->issetVideoPublisherOptions['consumer_key'])) {
            return $this->issetVideoPublisherOptions['consumer_key'];
        }
        return '';
    }

    public function getPrivateKey()
    {
        if (null === $this->issetVideoPublisherOptions) {
            $this->initOptions();
        }

        if(isset($this->issetVideoPublisherOptions['private_key'])) {
            return $this->issetVideoPublisherOptions['private_key'];
        }
        return '';
    }

    /**
     * @return IssetVideoPublisher
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
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
                return $body->view->video_player;
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
                    'uuid' => 'default',
                ],
                $params
            )
        );
        update_option(self::PUBLISHER_TOKEN_KEY, false);
        $video_url = $this->getVideoUrl($uuid);

        if ($video_url) {
            $result = sprintf('<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="%s"></iframe></div>', $video_url);
        } else {
            $result = 'Video not found.';
        }

        return $result;
    }
}