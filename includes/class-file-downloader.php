<?php
class SFD_File_Downloader {
    public static function init() {
        add_action('init', [self::class, 'handle_download_request']);
        add_filter('the_content', [self::class, 'add_download_buttons']);
    }
    
    public static function handle_download_request() {
        if (isset($_GET['sfd_download']) && isset($_GET['file'])) {
            $file_url = SFD_URL_Encryptor::decrypt_url($_GET['file']);
            $post_id = intval($_GET['post']);
            
            if (self::can_download($post_id)) {
                $file_path = str_replace(site_url('/'), ABSPATH, $file_url);
                
                if (file_exists($file_path)) {
                    header('Content-Type: ' . mime_content_type($file_path));
                    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                    readfile($file_path);
                    exit;
                }
            }
        }
    }
    
    public static function can_download($post_id) {
        $requires_login = get_post_meta($post_id, '_file_requires_login', true);
        return !$requires_login || is_user_logged_in();
    }
    
    public static function add_download_buttons($content) {
        global $post;
        
        if (!is_singular() || !is_main_query()) {
            return $content;
        }
        
        $download_html = '';
        
        for ($i = 1; $i <= 2; $i++) {
            $encrypted_url = get_post_meta($post->ID, "_secure_file_url_{$i}", true);
            if ($encrypted_url) {
                $requires_login = get_post_meta($post->ID, "_file_{$i}_requires_login", true);
                
                if (!$requires_login || is_user_logged_in()) {
                    $download_url = add_query_arg([
                        'sfd_download' => '1',
                        'file' => $encrypted_url,
                        'post' => $post->ID
                    ], site_url());
                    
                    $download_html .= self::get_download_button_html($download_url, $i);
                } elseif ($requires_login) {
                    $download_html .= self::get_login_message_html($i);
                }
            }
        }
        
        return $content . $download_html;
    }
    
    private static function get_download_button_html($url, $index) {
        return sprintf(
            '<div class="sfd-download-button">
                <a href="%s" class="button">
                    Download File %d
                </a>
            </div>',
            esc_url($url),
            $index
        );
    }
    
    private static function get_login_message_html($index) {
        return sprintf(
            '<div class="sfd-login-required">
                <p>Please <a href="%s">login</a> to download File %d</p>
            </div>',
            esc_url(wp_login_url(get_permalink())),
            $index
        );
    }
}