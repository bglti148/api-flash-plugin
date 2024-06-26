<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Generate_Screenshot {
    
    private static $instance = null;

    private function __construct() {
        $this->define_hooks();
    }

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function define_hooks() {
        add_filter( 'post_row_actions', array( $this, 'add_generate_screenshot_link' ), 10, 2 );
        add_filter( 'page_row_actions', array( $this, 'add_generate_screenshot_link' ), 10, 2 );
        add_action( 'admin_init', array( $this, 'handle_generate_screenshot' ) );
        add_action( 'admin_notices', array( $this, 'screenshot_admin_notice' ) );
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_generate_screenshot_link( $actions, $post ) {
        $post_types = array( 'post', 'page' ); // Modify post types here

        if ( in_array( $post->post_type, $post_types ) ) {
            $actions['generate_screenshot'] = '<a href="' . admin_url( 'admin.php?action=generate_screenshot&post=' . $post->ID ) . '">Generate Screenshot</a>';
        }

        return $actions;
    }

    public function handle_generate_screenshot() {
        if ( isset( $_GET['action'] ) && $_GET['action'] == 'generate_screenshot' && isset( $_GET['post'] ) ) {
            $post_id = intval( $_GET['post'] );
            $post = get_post( $post_id );

            if ( in_array( $post->post_type, array( 'post', 'page' ) ) ) { // Modify post types here
                $screenshot_url = $this->generate_screenshot_for_post( $post );

                if ( $screenshot_url ) {
                    $redirect_url = add_query_arg( array(
                        'post_type' => $post->post_type,
                        'screenshot_url' => urlencode( $screenshot_url ),
                    ), admin_url( 'edit.php' ) );
                } else {
                    $redirect_url = add_query_arg( array(
                        'post_type' => $post->post_type,
                        'screenshot_error' => 'true',
                    ), admin_url( 'edit.php' ) );
                }

                wp_redirect( $redirect_url );
                exit;
            }
        }
    }

    private function generate_screenshot_for_post( $post ) {
        $access_key = get_option( 'generate_screenshot_api_key' );
        if ( ! $access_key ) {
            error_log( 'Screenshot API key is not set.' );
            return false;
        }
        
        $post_url = urlencode( get_permalink( $post->ID ) );
        $element = urlencode( '.demo-ui-block' );
        $api_url = "https://api.apiflash.com/v1/urltoimage?access_key={$access_key}&url={$post_url}&format=webp&fresh=true&quality=100&element={$element}";

        $response = wp_remote_get( $api_url, array( 'timeout' => 120 ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'Screenshot API Error: ' . $response->get_error_message() );
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code != 200 ) {
            error_log( 'Screenshot API returned response code: ' . $response_code );
            return false;
        }

        $image_data = wp_remote_retrieve_body( $response );
        if ( ! $image_data ) {
            error_log( 'Screenshot API returned empty body.' );
            return false;
        }

        $upload = wp_upload_bits( "screenshot_{$post->ID}.webp", null, $image_data );
        if ( $upload['error'] ) {
            error_log( 'Image upload error: ' . $upload['error'] );
            return false;
        }

        $attachment_id = $this->create_attachment_from_upload( $upload, $post->ID );
        if ( $attachment_id ) {
            set_post_thumbnail( $post->ID, $attachment_id );
            return wp_get_attachment_url( $attachment_id );
        }

        error_log( 'Failed to create attachment from upload.' );
        return false;
    }

    private function create_attachment_from_upload( $upload, $post_id ) {
        $filetype = wp_check_filetype( $upload['file'] );
        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title' => sanitize_file_name( $upload['file'] ),
            'post_content' => '',
            'post_status' => 'inherit',
        );

        $attachment_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
        wp_update_attachment_metadata( $attachment_id, $attach_data );

        return $attachment_id;
    }

    public function screenshot_admin_notice() {
        if ( isset( $_GET['screenshot_url'] ) ) {
            $screenshot_url = esc_url( $_GET['screenshot_url'] );
            echo '<div class="notice notice-success is-dismissible"><p>Screenshot generated: <a href="' . $screenshot_url . '" target="_blank">View Screenshot</a></p></div>';
        } elseif ( isset( $_GET['screenshot_error'] ) ) {
            echo '<div class="notice notice-error is-dismissible"><p>Failed to generate screenshot.</p></div>';
        }
    }

    public function add_settings_page() {
        add_options_page(
            'Screenshot Configs',
            'Screenshot Configs',
            'manage_options',
            'screenshot-configs',
            array( $this, 'create_settings_page' )
        );
    }

    public function create_settings_page() {
        ?>
        <div class="wrap">
            <h1>Screenshot Configs</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'generate_screenshot_settings_group' );
                do_settings_sections( 'screenshot-configs' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting( 'generate_screenshot_settings_group', 'generate_screenshot_api_key' );

        add_settings_section(
            'generate_screenshot_settings_section',
            'API Configuration',
            null,
            'screenshot-configs'
        );

        add_settings_field(
            'generate_screenshot_api_key',
            'API Key',
            array( $this, 'api_key_field_callback' ),
            'screenshot-configs',
            'generate_screenshot_settings_section'
        );
    }

    public function api_key_field_callback() {
        $api_key = get_option( 'generate_screenshot_api_key' );
        echo '<input type="text" name="generate_screenshot_api_key" value="' . esc_attr( $api_key ) . '" />';
    }
}
