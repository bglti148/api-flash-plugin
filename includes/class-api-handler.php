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

        $options = get_option('generate_screenshot_screenshot_settings', array());

        $params = array(
            'access_key' => $access_key,
            'url' => get_permalink($post->ID)
        );

        $api_params = array(
            'format', 'width', 'height', 'fresh', 'full_page', 'quality', 'delay', 'scroll_page',
            'ttl', 'response_type', 'thumbnail_width', 'crop', 'no_cookie_banners', 'no_ads',
            'no_tracking', 'scale_factor', 'element', 'element_overlap', 'user_agent', 'extract_html',
            'extract_text', 'transparent', 'wait_for', 'wait_until', 'fail_on_status', 'accept_language',
            'css', 'cookies', 'proxy', 'latitude', 'longitude', 'accuracy', 'js', 'headers', 'time_zone',
            'ip_location', 's3_access_key_id', 's3_secret_key', 's3_bucket', 's3_key', 's3_endpoint', 's3_region'
        );

        foreach ($api_params as $param) {
            if (isset($options[$param]) && !empty($options[$param])) {
                $params[$param] = $options[$param];
            }
        }

        $api_url = "https://api.apiflash.com/v1/urltoimage?" . http_build_query($params);

        // Log the API request URL for debugging
        error_log('API Request URL: ' . $api_url);

        $response = wp_remote_get($api_url, array('timeout' => 120));

        // Log the raw response for debugging
        error_log('API Response: ' . print_r($response, true));

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
