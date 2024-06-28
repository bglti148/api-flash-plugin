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

    
    // Callback function for screenshot type
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



    //Callback function for scale setting
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

    // You'll add specific settings field callbacks here later
}