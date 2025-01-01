<?php
class SFD_Legacy_File_Handler {
    public static function init() {
        add_filter('the_content', [self::class, 'add_legacy_download_buttons']);
        add_action('init', [self::class, 'handle_legacy_download']);
    }

    public static function handle_legacy_download() {
        if (isset($_GET['sfd_legacy']) && isset($_GET['file'])) {
            // Verify the nonce
            if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'sfd_legacy_download')) {
                wp_die(__('Invalid nonce verification.', 'text-domain'), 403);
            }

            $post_id = intval($_GET['post']);
            $stored_url = get_post_meta($post_id, '_json_file_url', true);

            if ($stored_url) {
                $file_path = str_replace(site_url('/'), ABSPATH, $stored_url);

                if (file_exists($file_path)) {
                    // Prevent any output before headers
                    if (ob_get_level()) {
                        ob_end_clean();
                    }

                    // Set headers for download
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file_path));

                    // Clear output buffer and read the file
                    flush();
                    readfile($file_path);
                    exit;
                } else {
                    wp_die(__('File not found.', 'text-domain'), 404);
                }
            } else {
                wp_die(__('No file URL found.', 'text-domain'), 404);
            }
        }
    }

    public static function add_legacy_download_buttons($content) {
        global $post;

        if (!is_singular() || !is_main_query()) {
            return $content;
        }

        // Check for legacy file URL
        $json_file_url = get_post_meta($post->ID, '_json_file_url', true);
        if (!$json_file_url) {
            return $content;
        }

        $file_extension = strtolower(pathinfo($json_file_url, PATHINFO_EXTENSION));
        $download_type = ($file_extension === 'json') ? 'Workflows' : 'LoRA';

        // Create secure download URL with nonce
        $download_url = add_query_arg([
            'sfd_legacy' => '1',
            'file' => '1',
            'post' => $post->ID,
            'nonce' => wp_create_nonce('sfd_legacy_download'),
        ], site_url());

        $download_button = sprintf(
            '<div class="sfd-download-button">
                <a href="%s" class="button">
                    Download %s
                </a>
            </div>',
            esc_url($download_url),
            esc_html($download_type)
        );

        return $content . $download_button;
    }
}

// Initialize the class
SFD_Legacy_File_Handler::init();
