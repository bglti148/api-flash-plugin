<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once WP_GENERATE_SCREENSHOT_PLUGIN_DIR . 'includes/admin/class-api-key-tab.php';
require_once WP_GENERATE_SCREENSHOT_PLUGIN_DIR . 'includes/admin/class-post-types-tab.php';

class WP_Generate_Screenshot_Admin_Settings {
    private $tabs = array();

    public function __construct() {
        $this->tabs = array(
            'api_key' => new WP_Generate_Screenshot_API_Key_Tab(),
            'post_types' => new WP_Generate_Screenshot_Post_Types_Tab(),
        );
    }

    public function add_settings_page() {
        add_options_page(
            'WP Screenshot',
            'WP Screenshot',
            'manage_options',
            'wp-screenshot',
            array($this, 'create_settings_page')
        );
    
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function create_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'api_key';
        ?>
        <div class="wrap">
            <h1>WP Screenshot</h1>
            <h2 class="nav-tab-wrapper">
                <?php
                foreach ($this->tabs as $tab_key => $tab_instance) {
                    $active_class = ($active_tab == $tab_key) ? 'nav-tab-active' : '';
                    echo "<a href='?page=wp-screenshot&tab={$tab_key}' class='nav-tab {$active_class}'>{$tab_instance->get_tab_title()}</a>";
                }
                ?>
            </h2>
            <form method="post" action="options.php">
                <?php
                if (isset($this->tabs[$active_tab])) {
                    $this->tabs[$active_tab]->render();
                }
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        foreach ($this->tabs as $tab_instance) {
            $tab_instance->register_settings();
        }
    }
}