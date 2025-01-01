<?php
class SFD_File_Uploader {
    public static function init() {
        add_filter('upload_mimes', [self::class, 'add_allowed_mime_types']);
    }
    
    public static function add_allowed_mime_types($mimes) {
        $mimes['json'] = 'application/json';
        $mimes['safetensors'] = 'application/octet-stream';
        return $mimes;
    }
    
    public static function handle_file_upload($file, $post_id, $index) {
        if (empty($file['name'])) {
            return false;
        }
        
        $allowed_types = ['json', 'safetensors'];
        $file_type = wp_check_filetype(basename($file['name']));
        
        if (!in_array($file_type['ext'], $allowed_types)) {
            return false;
        }
        
        $upload = wp_handle_upload($file, ['test_form' => false]);
        
        if ($upload && !isset($upload['error'])) {
            $attachment_id = wp_insert_attachment([
                'guid' => $upload['url'],
                'post_mime_type' => $upload['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
                'post_content' => '',
                'post_status' => 'inherit'
            ], $upload['file']);
            
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
            wp_update_attachment_metadata($attachment_id, $attach_data);
            
            // Store encrypted URL
            $encrypted_url = SFD_URL_Encryptor::encrypt_url($upload['url']);
            update_post_meta($post_id, "_secure_file_url_{$index}", $encrypted_url);
            
            return $attachment_id;
        }
        
        return false;
    }
}