<?php
if (!defined('ABSPATH')) {
    exit;
}

class Generate_Screenshot {
    private static $instance = null;

    private function __construct() {
        $this->load_dependencies();
        $this->define_hooks();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function load_dependencies() {
        require_once WP_GENERATE_SCREENSHOT_PLUGIN_DIR . 'includes/admin/class-admin-settings.php';
        require_once WP_GENERATE_SCREENSHOT_PLUGIN_DIR . 'includes/class-api-handler.php';
    }

    private function define_hooks() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_filter('post_row_actions', array($this, 'add_generate_screenshot_link'), 10, 2);
        add_filter('page_row_actions', array($this, 'add_generate_screenshot_link'), 10, 2);
        add_action('admin_init', array($this, 'handle_generate_screenshot'));
        add_action('admin_notices', array($this, 'screenshot_admin_notice'));
    }

    public function add_settings_page() {
        $admin_settings = new WP_Generate_Screenshot_Admin_Settings();
        $admin_settings->add_settings_page();
    }

    public function add_generate_screenshot_link($actions, $post) {
        $enabled_post_types = get_option('generate_screenshot_post_types', array());
    
        if (in_array($post->post_type, $enabled_post_types)) {
            $actions['generate_screenshot'] = '<a href="' . admin_url('admin.php?action=generate_screenshot&post=' . $post->ID) . '">Generate Screenshot</a>';
        }
    
        return $actions;
    }

    public function handle_generate_screenshot() {
        if (isset($_GET['action']) && $_GET['action'] == 'generate_screenshot' && isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            $post = get_post($post_id);
            $enabled_post_types = get_option('generate_screenshot_post_types', array());
    
            if (in_array($post->post_type, $enabled_post_types)) {
                $api_handler = new WP_Generate_Screenshot_API_Handler();
                $screenshot_url = $api_handler->generate_screenshot_for_post($post);
    
                if ($screenshot_url) {
                    set_transient('wp_generate_screenshot_notice', array(
                        'type' => 'success',
                        'url' => $screenshot_url
                    ), 60); // Store for 60 seconds
                } else {
                    set_transient('wp_generate_screenshot_notice', array(
                        'type' => 'error'
                    ), 60); // Store for 60 seconds
                }
    
                wp_safe_redirect(admin_url('edit.php?post_type=' . $post->post_type));
                exit;
            }
        }
    }

    public function screenshot_admin_notice() {
        $notice = get_transient('wp_generate_screenshot_notice');
        if ($notice) {
            if ($notice['type'] === 'success') {
                echo '<div class="notice notice-success is-dismissible"><p>Screenshot generated: <a href="' . esc_url($notice['url']) . '" target="_blank">View Screenshot</a></p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to generate screenshot.</p></div>';
            }
            delete_transient('wp_generate_screenshot_notice');
        }
    }
}