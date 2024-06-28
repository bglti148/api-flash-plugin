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
        register_setting(
            'wp_screenshot_settings_settings',
            'generate_screenshot_settings',
            array($this, 'sanitize_settings')
        );
    
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

        add_settings_field(
            'screenshot_type',
            'Screenshot Type',
            array($this, 'type_field_callback'),
            'wp-screenshot-settings',
            'generate_screenshot_settings_section'
        );

        add_settings_field(
            'screenshot_scale',
            'Screenshot Scale',
            array($this, 'scale_field_callback'),
            'wp-screenshot-settings',
            'generate_screenshot_settings_section'
        );

        add_settings_field(
            'screenshot_delay',
            'Delay (in seconds)',
            array($this, 'delay_field_callback'),
            'wp-screenshot-settings',
            'generate_screenshot_settings_section'
        );
    
        add_settings_field(
            'screenshot_scroll_page',
            'Scroll page before screenshot?',
            array($this, 'scroll_page_field_callback'),
            'wp-screenshot-settings',
            'generate_screenshot_settings_section'
        );
    }

    // Specific setting field callbacks

    public function settings_section_callback() {
        echo '<p>Configure various settings for screenshot generation:</p>';
    }

    // Image format setting
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

    
    // Screenshot width setting
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

    
    // Screenshot type
    public function type_field_callback() {
        $options = get_option('generate_screenshot_settings');
        $type = isset($options['type']) ? $options['type'] : 'full_page';
        $css_selector = isset($options['css_selector']) ? $options['css_selector'] : '';
        $capture_overlapping = isset($options['capture_overlapping_elements']) ? $options['capture_overlapping_elements'] : 'false';
        
        echo "<label><input type='radio' name='generate_screenshot_settings[type]' value='full_page' " . checked($type, 'full_page', false) . " /> Full Page</label><br />";
        echo "<label><input type='radio' name='generate_screenshot_settings[type]' value='css_selector' " . checked($type, 'css_selector', false) . " /> CSS Selector</label><br />";
        
        echo "<div id='css_selector_options' style='margin-top: 10px; " . ($type === 'css_selector' ? '' : 'display: none;') . "'>";
        echo "<input type='text' name='generate_screenshot_settings[css_selector]' value='" . esc_attr($css_selector) . "' placeholder='Enter CSS selector' style='width: 100%;' />";
        
        echo "<div style='margin-top: 10px;'>";
        echo "<h4>Capture Overlapping Elements?</h4>";
        echo "<label><input type='radio' name='generate_screenshot_settings[capture_overlapping_elements]' value='true' " . checked($capture_overlapping, 'true', false) . " /> Yes</label><br />";
        echo "<label><input type='radio' name='generate_screenshot_settings[capture_overlapping_elements]' value='false' " . checked($capture_overlapping, 'false', false) . " /> No</label><br />";
        echo "<p class='description'>Choose whether to capture elements that overlap the target element when using CSS selector.</p>";
        echo "</div>";
        
        echo "</div>";
        
        echo "<p class='description'>Choose whether to capture the full page or a specific element using a CSS selector.</p>";
        
        // Add JavaScript to show/hide CSS selector input and Capture Overlapping Elements
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            function toggleCssSelectorOptions() {
                if ($('input[name="generate_screenshot_settings[type]"]:checked').val() === 'css_selector') {
                    $('#css_selector_options').show();
                } else {
                    $('#css_selector_options').hide();
                }
            }

            $('input[name="generate_screenshot_settings[type]"]').change(toggleCssSelectorOptions);
            toggleCssSelectorOptions(); // Call on page load
        });
        </script>
        <?php
    }



    // Scale setting
    public function scale_field_callback() {
        $options = get_option('generate_screenshot_settings');
        $scale = isset($options['scale']) ? $options['scale'] : '1';
        
        $scales = array(
            '1' => '1x',
            '2' => '2x',
        );
    
        foreach ($scales as $value => $label) {
            echo "<label><input type='radio' name='generate_screenshot_settings[scale]' value='{$value}' " . checked($scale, $value, false) . " /> {$label}</label><br />";
        }
        echo "<p class='description'>Select the scale factor for the screenshot. Higher values result in higher resolution images.</p>";
    }

    // Delay setting
    public function delay_field_callback() {
        $options = get_option('generate_screenshot_settings');
        $delay = isset($options['delay']) ? $options['delay'] : 0;
        
        echo "<input type='number' name='generate_screenshot_settings[delay]' value='" . esc_attr($delay) . "' min='0' max='10' step='1' />";
        echo "<p class='description'>Set the delay in seconds (0-10) to wait before capturing the screenshot.</p>";
    }
    
    // Scroll page setting
    public function scroll_page_field_callback() {
        $options = get_option('generate_screenshot_settings');
        $scroll_page = isset($options['scroll_page']) ? $options['scroll_page'] : 'false';
        
        echo "<label><input type='radio' name='generate_screenshot_settings[scroll_page]' value='true' " . checked($scroll_page, 'true', false) . " /> On</label><br />";
        echo "<label><input type='radio' name='generate_screenshot_settings[scroll_page]' value='false' " . checked($scroll_page, 'false', false) . " /> Off</label><br />";
        echo "<p class='description'>Choose whether to scroll through the entire page before capturing a screenshot.</p>";
    }


    //Santize fields and validate data
    //Scroll page setting field validation
    public function sanitize_settings($input) {
        $sanitized_input = array();
    
        // Sanitize delay
        if (isset($input['delay'])) {
            $sanitized_input['delay'] = intval($input['delay']);
            if ($sanitized_input['delay'] < 0 || $sanitized_input['delay'] > 10) {
                $sanitized_input['delay'] = 0; // Default to 0 if out of range
            }
        }
    
        // Sanitize scroll_page
        if (isset($input['scroll_page'])) {
            $sanitized_input['scroll_page'] = ($input['scroll_page'] === 'true') ? 'true' : 'false';
        }
    
        // ... sanitize other fields ...
    
        return $sanitized_input;
    }
}