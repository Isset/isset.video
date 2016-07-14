<?php

/**
 * Class IssetVideoPublisher.
 */
class IssetVideoPublisherMetaBox
{
    /**
     * @var IssetVideoPublisherMetaBox
     */
    private static $instance;

    /**
     * IssetVideoPublisherMetaBox constructor.
     */
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
    }


    public function add_meta_boxes()
    {
        $issetVideoPublisher = IssetVideoPublisher::instance();
        add_meta_box('video-publisher-preview', __('Publisher preview', 'isset-video-publisher'), [$this, 'metaBoxCallback'], $issetVideoPublisher->getPostType());
    }

    public function metaBoxCallback()
    {
        $strPublish = sprintf('[publish post_id=%d]', get_the_ID());

        _e('Use this shortcode in a post to include the video.', 'isset-video-publisher');
        echo '<br><br><code>' . $strPublish . '</code><hr>';
        echo do_shortcode($strPublish);
    }

    /**
     * @return IssetVideoPublisherMetaBox
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

}
