<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Generate_Screenshot_API_Key_Tab {
    public function get_tab_title() {
        return 'API Key';
    }

    public function render() {
        settings_fields('wp_screenshot_api_settings');
        do_settings_sections('wp-screenshot-api');
    }

    public function register_settings() {
        register_setting('wp_screenshot_api_settings', 'generate_screenshot_api_key');

        add_settings_section(
            'generate_screenshot_api_settings_section',
            'API Configuration',
            null,
            'wp-screenshot-api'
        );

        add_settings_field(
            'generate_screenshot_api_key',
            'API Key',
            array($this, 'api_key_field_callback'),
            'wp-screenshot-api',
            'generate_screenshot_api_settings_section'
        );
    }

    public function api_key_field_callback() {
        $api_key = get_option('generate_screenshot_api_key');
        echo '<input type="text" name="generate_screenshot_api_key" value="' . esc_attr($api_key) . '" />';
    }
}