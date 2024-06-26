<?php
/**
 * Plugin Name: Generate Screenshot
 * Description: Adds a "Generate Screenshot" option to post types, uses apiflash.com to capture screenshots and set them as featured images.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: wp-generate-screenshot
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'WP_GENERATE_SCREENSHOT_VERSION', '1.0.0' );
define( 'WP_GENERATE_SCREENSHOT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WP_GENERATE_SCREENSHOT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include the main plugin class.
require_once WP_GENERATE_SCREENSHOT_PLUGIN_DIR . 'includes/class-generate-screenshot.php';

// Initialize the plugin.
function wp_generate_screenshot_init() {
    Generate_Screenshot::get_instance();
}
add_action( 'plugins_loaded', 'wp_generate_screenshot_init' );
