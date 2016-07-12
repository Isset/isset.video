<?php


class IssetVideoPublisherAdminPage
{
    /**
     * @var IssetVideoPublisherAdminPage
     */
    private static $instance;

    /**
     * Holds the values to be used in the fields callbacks.
     */
    private $options;

    /**
     * Start up.
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_init', [$this, 'admin_init']);
    }

    /**
     * @return IssetVideoPublisherAdminPage
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Add options page.
     */
    public function admin_menu()
    {
        // This page will be under "Settings"
        add_options_page(
            __('Video Publisher Settings', 'isset-video-publisher'),
            __('Video Publisher Settings', 'isset-video-publisher'),
            'manage_options',
            'isset-video-publisher-admin',
            [$this, 'create_admin_page']
        );
    }

    /**
     * Options page callback.
     */
    public function create_admin_page()
    {
        ?>
        <div class="wrap">
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields('video_publisher_settings');
                do_settings_sections('publisher-admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings.
     */
    public function admin_init()
    {
        register_setting(
            'video_publisher_settings', // Option group
            'isset-video-publisher-options', // Option name
            [$this, 'sanitize'] // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            __('Video Publisher Settings', 'isset-video-publisher'), // Title
            [$this, 'print_section_info'], // Callback
            'publisher-admin' // Page
        );

        add_settings_field(
            'consumer_key', // ID
            'Consumer key', // Title
            [$this, 'consumer_key_callback'], // Callback
            'publisher-admin', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'private_key',
            'Private key',
            [$this, 'private_key_callback'],
            'publisher-admin',
            'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed.
     *
     * @param array $input Contains all settings fields as array keys
     *
     * @return array
     */
    public function sanitize($input)
    {
        $new_input = [];
        if (isset($input['consumer_key'])) {
            $new_input['consumer_key'] = sanitize_text_field($input['consumer_key']);
        }

        if (isset($input['private_key'])) {
            $new_input['private_key'] = sanitize_text_field($input['private_key']);
        }

        return $new_input;
    }

    /**
     * Print the Section text.
     */
    public function print_section_info()
    {
        _e('Enter your settings below:', 'isset-video-publisher');
    }

    /**
     * Get the settings option array and print one of its values.
     */
    public function consumer_key_callback()
    {
        $issetVideoPublisher = IssetVideoPublisher::instance();
        printf(
            '<input type="text" id="consumer_key" name="isset-video-publisher-options[consumer_key]" value="%s" />',
            $issetVideoPublisher->getConsumerKey()
        );
    }

    /**
     * Get the settings option array and print one of its values.
     */
    public function private_key_callback()
    {
        $issetVideoPublisher = IssetVideoPublisher::instance();
        printf(
            '<input type="text" id="private_key" name="isset-video-publisher-options[private_key]" value="%s" />',
            $issetVideoPublisher->getPrivateKey()
        );
    }
}
