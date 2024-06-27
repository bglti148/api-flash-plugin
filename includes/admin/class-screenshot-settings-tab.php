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
    
        add_settings_field(
            'screenshot_format',
            'Screenshot Format',
            array($this, 'format_field_callback'),
            'wp-screenshot-settings',
            'generate_screenshot_settings_section'
        );

        add_settings_field(
            'screenshot_width',
            'Resolution Width',
            array($this, 'width_field_callback'),
            'wp-screenshot-settings',
            'generate_screenshot_settings_section'
        );
    }

    public function settings_section_callback() {
        echo '<p>Configure various settings for screenshot generation:</p>';
    }

    // Call back function for image format setting
    public function format_field_callback() {
        $options = get_option('generate_screenshot_settings');
        $format = isset($options['format']) ? $options['format'] : 'jpeg';
        
        $formats = array(
            'jpeg' => 'JPEG',
            'png' => 'PNG',
            'webp' => 'WebP'
        );
    
        foreach ($formats as $value => $label) {
            echo "<label><input type='radio' name='generate_screenshot_settings[format]' value='{$value}' " . checked($format, $value, false) . " /> {$label}</label><br />";
        }
        echo "<p class='description'>Select the image format for the screenshots.</p>";
    }

    // Callback function for screenshot width setting
    public function width_field_callback() {
        $options = get_option('generate_screenshot_settings');
        $width = isset($options['width']) ? $options['width'] : '1920';
        
        $widths = array(
            '430' => '430px (iPhone 14 Pro Max)',
            '1024' => '1024px (Tablet)',
            '1366' => '1366px (Laptop)',
            '1920' => '1920px (Desktop)',
        );
    
        foreach ($widths as $value => $label) {
            echo "<label><input type='radio' name='generate_screenshot_settings[width]' value='{$value}' " . checked($width, $value, false) . " /> {$label}</label><br />";
        }
        echo "<p class='description'>Select the width of the screenshot. This affects the resolution of the captured image.</p>";
    }
    // You'll add specific settings field callbacks here later
}