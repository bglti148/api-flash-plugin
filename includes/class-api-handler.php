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
        
        // Get the screenshot settings
        $screenshot_settings = get_option('generate_screenshot_settings', array());
        $format = isset($screenshot_settings['format']) ? $screenshot_settings['format'] : 'jpeg';
        
        $api_url = "https://api.apiflash.com/v1/urltoimage?access_key={$access_key}&url={$post_url}&format={$format}&fresh=true&quality=100&element={$element}";
    
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
    
        // Use the correct file extension based on the format
        $file_extension = $format;
        if ($format === 'jpeg') {
            $file_extension = 'jpg';
        }
    
        $upload = wp_upload_bits("screenshot_{$post->ID}.{$file_extension}", null, $image_data);
        if ($upload['error']) {
            error_log('Image upload error: ' . $upload['error']);
            return false;
        }
    
        $attachment_id = $this->create_attachment_from_upload($upload, $post->ID, $format);
        if ($attachment_id) {
            set_post_thumbnail($post->ID, $attachment_id);
            return wp_get_attachment_url($attachment_id);
        }
    
        error_log('Failed to create attachment from upload.');
        return false;
    }

    private function create_attachment_from_upload($upload, $post_id, $format) {
        $file_path = $upload['file'];
        $file_name = basename($file_path);
    
        $file_type = wp_check_filetype($file_name, null);
        $attachment = array(
            'post_mime_type' => $file_type['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', $file_name),
            'post_content' => '',
            'post_status' => 'inherit'
        );
    
        $attachment_id = wp_insert_attachment($attachment, $file_path, $post_id);
        if (!is_wp_error($attachment_id)) {
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
        }
    
        return $attachment_id;
    }
}