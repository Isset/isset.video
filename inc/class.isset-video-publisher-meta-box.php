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
        add_action('save_post', [$this, 'savePost'], 10, 2);
    }


    public function add_meta_boxes()
    {
        $issetVideoPublisher = IssetVideoPublisher::instance();
        add_meta_box('video-publisher-preview', __('Publisher preview', 'isset-video-publisher'), [$this, 'metaBoxCallback'], $issetVideoPublisher->getPostType());
        add_meta_box('video-publisher-front-page', __('Front page video', 'video-publisher-preview'), [$this, 'metaBoxFrontPageback'], $issetVideoPublisher->getPostType(), 'side');
    }

    public function metaBoxCallback()
    {
        global $post;
        $strPublish = sprintf('[publish uuid=%s]', $post->post_name);

        _e('Use this shortcode in a post to include the video.', 'isset-video-publisher');
        echo '<br><br><code>' . $strPublish . '</code><hr>';
        echo do_shortcode($strPublish);
    }


    public function metaBoxFrontPageback()
    {
        $issetVideoPublisher = IssetVideoPublisher::instance();
        $checked = get_the_ID() === $issetVideoPublisher->getFrontPageId();
        wp_nonce_field(basename(__FILE__), 'isset_publisher_class_nonce'); ?>

        <label for="isset-video-publisher-home">
            <input type="checkbox" name="isset_video_publisher_home" id="isset-video-publisher-home"<?php echo $checked ? 'checked' : ''; ?>>
            <?php _e('Use as video on homepage.', 'isset-video-publisher'); ?>
        </label>
        <?php
    }

    public function savePost($post_id, $post)
    {
        if (!isset($_POST['isset_publisher_class_nonce']) || !wp_verify_nonce($_POST['isset_publisher_class_nonce'], basename(__FILE__))) {
            return $post_id;
        }
        $issetVideoPublisher = IssetVideoPublisher::instance();
        if(isset($_POST['isset_video_publisher_home'])) {
            $issetVideoPublisher->setFrontPageId(get_the_ID());
        } else {
            if(get_the_ID() === $issetVideoPublisher->getFrontPageId()) {
                $issetVideoPublisher->setFrontPageId(false);
            }
        }
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
