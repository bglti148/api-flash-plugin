<?php
if (!defined('ABSPATH')) {
    exit;
}

class WP_Generate_Screenshot_API_Handler {
    public function generate_screenshot_for_post($post) {
        $access_key = get_option('generate_screenshot_api_key');
        if (!$access_key) {
            error_log('Screenshot API key is not set.');
            return false;
        }
        
        $post_url = urlencode(get_permalink($post->ID));
        $element = urlencode('.demo-ui-block');
        $api_url = "https://api.apiflash.com/v1/urltoimage?access_key={$access_key}&url={$post_url}&format=webp&fresh=true&quality=100&element={$element}";

        $response = wp_remote_get($api_url, array('timeout' => 120));

        if (is_wp_error($response)) {
            error_log('Screenshot API Error: ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code != 200) {
            error_log('Screenshot API returned response code: ' . $response_code);
            return false;
        }

        $image_data = wp_remote_retrieve_body($response);
        if (!$image_data) {
            error_log('Screenshot API returned empty body.');
            return false;
        }

        $upload = wp_upload_bits("screenshot_{$post->ID}.webp", null, $image_data);
        if ($upload['error']) {
            error_log('Image upload error: ' . $upload['error']);
            return false;
        }

        $attachment_id = $this->create_attachment_from_upload($upload, $post->ID);
        if ($attachment_id) {
            set_post_thumbnail($post->ID, $attachment_id);
            return wp_get_attachment_url($attachment_id);
        }

        error_log('Failed to create attachment from upload.');
        return false;
    }

    private function create_attachment_from_upload($upload, $post_id) {
        $filetype = wp_check_filetype($upload['file']);
        $attachment = array(
            'post_mime_type' => $filetype['type'],
            'post_title' => sanitize_file_name($upload['file']),
            'post_content' => '',
            'post_status' => 'inherit',
        );

        $attachment_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attach_data);

        return $attachment_id;
    }
}