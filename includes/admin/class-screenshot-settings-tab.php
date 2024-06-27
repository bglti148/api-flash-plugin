<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Generate_Screenshot_Screenshot_Settings_Tab {
    public function get_tab_title() {
        return 'Screenshot Settings';
    }

    public function render() {
        settings_fields('wp_screenshot_settings_settings');
        do_settings_sections('wp-screenshot-settings');
    }

    public function register_settings() {
        register_setting('wp_screenshot_settings_settings', 'generate_screenshot_settings');

        add_settings_section(
            'generate_screenshot_settings_section',
            'Configure Screenshot Settings',
            array($this, 'settings_section_callback'),
            'wp-screenshot-settings'
        );

        // You'll add specific settings fields here later
    }

    public function settings_section_callback() {
        echo '<p>Configure various settings for screenshot generation:</p>';
    }

    // You'll add specific settings field callbacks here later
}