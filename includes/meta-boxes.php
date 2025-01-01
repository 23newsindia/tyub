<?php
class SFD_Meta_Boxes {
    public static function init() {
        add_action('add_meta_boxes', [self::class, 'add_meta_boxes']);
        add_action('save_post', [self::class, 'save_meta_boxes']);
    }
    
    public static function add_meta_boxes() {
        add_meta_box(
            'sfd_file_upload_box',
            'Secure File Uploads',
            [self::class, 'render_meta_box'],
            'post',
            'normal',
            'high'
        );
    }
    
    public static function render_meta_box($post) {
        wp_nonce_field('sfd_file_upload', 'sfd_nonce');
        
        for ($i = 1; $i <= 2; $i++) {
            $requires_login = get_post_meta($post->ID, "_file_{$i}_requires_login", true);
            ?>
            <div class="sfd-file-upload-section">
                <h4>File Upload <?php echo $i; ?></h4>
                <p>
                    <label>
                        <input type="file" name="secure_file_<?php echo $i; ?>" 
                               accept=".json,.safetensors">
                    </label>
                </p>
                <p>
                    <label>
                        <input type="checkbox" name="file_<?php echo $i; ?>_requires_login" 
                               value="1" <?php checked($requires_login, '1'); ?>>
                        Require login to download
                    </label>
                </p>
            </div>
            <?php
        }
    }
    
    public static function save_meta_boxes($post_id) {
        if (!isset($_POST['sfd_nonce']) || !wp_verify_nonce($_POST['sfd_nonce'], 'sfd_file_upload')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        for ($i = 1; $i <= 2; $i++) {
            // Save login requirement
            $requires_login = isset($_POST["file_{$i}_requires_login"]) ? '1' : '0';
            update_post_meta($post_id, "_file_{$i}_requires_login", $requires_login);
            
            // Handle file upload
            if (!empty($_FILES["secure_file_{$i}"]['name'])) {
                SFD_File_Uploader::handle_file_upload($_FILES["secure_file_{$i}"], $post_id, $i);
            }
        }
    }
}