<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Generate_Screenshot_Admin_Settings {
    public function add_settings_page() {
        add_options_page(
            'Generate Screenshot Settings',
            'Generate Screenshot',
            'manage_options',
            'generate-screenshot-settings',
            array($this, 'render_settings_page')
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Generate Screenshot Settings</h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=generate-screenshot-settings&tab=general" class="nav-tab <?php echo $this->get_active_tab() == 'general' ? 'nav-tab-active' : ''; ?>">General</a>
                <a href="?page=generate-screenshot-settings&tab=post_types" class="nav-tab <?php echo $this->get_active_tab() == 'post_types' ? 'nav-tab-active' : ''; ?>">Post Types</a>
                <a href="?page=generate-screenshot-settings&tab=screenshot" class="nav-tab <?php echo $this->get_active_tab() == 'screenshot' ? 'nav-tab-active' : ''; ?>">Screenshot Settings</a>
            </h2>
            <form method="post" action="options.php">
                <?php
                if ($this->get_active_tab() == 'general') {
                    settings_fields('generate_screenshot_general_settings');
                    do_settings_sections('generate-screenshot-settings-general');
                } elseif ($this->get_active_tab() == 'post_types') {
                    settings_fields('generate_screenshot_post_types_settings');
                    do_settings_sections('generate-screenshot-settings-post_types');
                } else {
                    settings_fields('generate_screenshot_screenshot_settings');
                    do_settings_sections('generate-screenshot-settings-screenshot');
                }
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    private function get_active_tab() {
        return isset($_GET['tab']) ? $_GET['tab'] : 'general';
    }

    public function initialize_settings() {
        // General Settings Section
        add_settings_section(
            'generate_screenshot_general_section',
            'General Settings',
            null,
            'generate-screenshot-settings-general'
        );

        add_settings_field(
            'generate_screenshot_api_key',
            'API Key',
            array($this, 'render_api_key_field'),
            'generate-screenshot-settings-general',
            'generate_screenshot_general_section'
        );

        register_setting('generate_screenshot_general_settings', 'generate_screenshot_api_key');

        // Post Types Settings Section
        add_settings_section(
            'generate_screenshot_post_types_section',
            'Post Types Settings',
            null,
            'generate-screenshot-settings-post_types'
        );

        add_settings_field(
            'generate_screenshot_post_types',
            'Select Post Types',
            array($this, 'render_post_types_field'),
            'generate-screenshot-settings-post_types',
            'generate_screenshot_post_types_section'
        );

        register_setting('generate_screenshot_post_types_settings', 'generate_screenshot_post_types', array('sanitize_callback' => array($this, 'sanitize_post_types')));

        // Screenshot Settings Section
        add_settings_section(
            'generate_screenshot_screenshot_section',
            'Screenshot Settings',
            null,
            'generate-screenshot-settings-screenshot'
        );

        $this->add_screenshot_settings_fields();

        register_setting('generate_screenshot_screenshot_settings', 'generate_screenshot_screenshot_settings');
    }

    public function render_api_key_field() {
        $value = get_option('generate_screenshot_api_key', '');
        echo '<input type="text" name="generate_screenshot_api_key" value="' . esc_attr($value) . '" class="regular-text">';
    }

    public function render_post_types_field() {
        $post_types = get_post_types(array('public' => true), 'objects');
        $selected_post_types = get_option('generate_screenshot_post_types', array());
        if (!is_array($selected_post_types)) {
            $selected_post_types = array();
        }
        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $selected_post_types) ? 'checked' : '';
            echo '<label><input type="checkbox" name="generate_screenshot_post_types[]" value="' . esc_attr($post_type->name) . '" ' . $checked . '> ' . esc_html($post_type->label) . '</label><br>';
        }
    }

    public function sanitize_post_types($input) {
        if (!is_array($input)) {
            return array();
        }
        return array_map('sanitize_text_field', $input);
    }

    private function add_screenshot_settings_fields() {
        $fields = array(
            'format' => 'Format (jpeg, png, webp)',
            'width' => 'Width (in pixels)',
            'height' => 'Height (in pixels)',
            'fresh' => 'Fresh (true/false)',
            'full_page' => 'Full Page (true/false)',
            'quality' => 'Quality (0-100)',
            'delay' => 'Delay (in seconds)',
            'scroll_page' => 'Scroll Page (true/false)',
            'ttl' => 'TTL (in seconds)',
            'response_type' => 'Response Type (image/json)',
            'thumbnail_width' => 'Thumbnail Width (in pixels)',
            'crop' => 'Crop (left,top,width,height)',
            'no_cookie_banners' => 'No Cookie Banners (true/false)',
            'no_ads' => 'No Ads (true/false)',
            'no_tracking' => 'No Tracking (true/false)',
            'scale_factor' => 'Scale Factor',
            'element' => 'Element (CSS selector)',
            'element_overlap' => 'Element Overlap (true/false)',
            'user_agent' => 'User Agent',
            'extract_html' => 'Extract HTML (true/false)',
            'extract_text' => 'Extract Text (true/false)',
            'transparent' => 'Transparent (true/false)',
            'wait_for' => 'Wait For (CSS selector)',
            'wait_until' => 'Wait Until (dom_loaded/page_loaded/network_idle)',
            'fail_on_status' => 'Fail On Status (comma-separated list)',
            'accept_language' => 'Accept Language',
            'css' => 'CSS',
            'cookies' => 'Cookies (name=value;name=value)',
            'proxy' => 'Proxy (address:port or user:password@address:port)',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'accuracy' => 'Accuracy (in meters)',
            'js' => 'JavaScript',
            'headers' => 'Headers (key=value;key=value)',
            'time_zone' => 'Time Zone',
            'ip_location' => 'IP Location',
            's3_access_key_id' => 'S3 Access Key ID',
            's3_secret_key' => 'S3 Secret Key',
            's3_bucket' => 'S3 Bucket',
            's3_key' => 'S3 Key',
            's3_endpoint' => 'S3 Endpoint',
            's3_region' => 'S3 Region',
        );

        foreach ($fields as $key => $label) {
            add_settings_field(
                'generate_screenshot_' . $key,
                $label,
                array($this, 'render_text_field'),
                'generate-screenshot-settings-screenshot',
                'generate_screenshot_screenshot_section',
                array('key' => $key)
            );
        }
    }

    public function render_text_field($args) {
        $options = get_option('generate_screenshot_screenshot_settings', array());
        $value = isset($options[$args['key']]) ? $options[$args['key']] : '';
        echo '<input type="text" name="generate_screenshot_screenshot_settings[' . $args['key'] . ']" value="' . esc_attr($value) . '" class="regular-text">';
    }
}

if (is_admin()) {
    $settings = new WP_Generate_Screenshot_Admin_Settings();
    add_action('admin_init', array($settings, 'initialize_settings'));
}

