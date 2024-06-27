<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Generate_Screenshot_Post_Types_Tab {
    public function get_tab_title() {
        return 'Post Types';
    }

    public function render() {
        settings_fields('wp_screenshot_post_types_settings');
        do_settings_sections('wp-screenshot-post-types');
    }

    public function register_settings() {
        register_setting('wp_screenshot_post_types_settings', 'generate_screenshot_post_types');

        add_settings_section(
            'generate_screenshot_post_types_section',
            'Select Post Types',
            array($this, 'post_types_section_callback'),
            'wp-screenshot-post-types'
        );

        add_settings_field(
            'generate_screenshot_post_types',
            'Enabled Post Types',
            array($this, 'post_types_field_callback'),
            'wp-screenshot-post-types',
            'generate_screenshot_post_types_section'
        );
    }

    public function post_types_section_callback() {
        echo '<p>Select the post types for which you want to enable screenshot generation:</p>';
    }

    public function post_types_field_callback() {
        $post_types = get_post_types(array('public' => true), 'objects');
        $enabled_post_types = get_option('generate_screenshot_post_types', array());

        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $enabled_post_types) ? 'checked' : '';
            echo "<label><input type='checkbox' name='generate_screenshot_post_types[]' value='{$post_type->name}' {$checked}> {$post_type->label}</label><br>";
        }
    }
}