<?php
// Load all required files
require_once plugin_dir_path(__FILE__) . 'class-file-uploader.php';
require_once plugin_dir_path(__FILE__) . 'class-file-downloader.php';
require_once plugin_dir_path(__FILE__) . 'class-url-encryptor.php';
require_once plugin_dir_path(__FILE__) . 'class-legacy-file-handler.php';
require_once plugin_dir_path(__FILE__) . 'meta-boxes.php';

// Initialize components
add_action('init', 'SFD_Init::init');

class SFD_Init {
    public static function init() {
        // Initialize URL encryption
        SFD_URL_Encryptor::init();
        
        // Initialize file upload handling
        SFD_File_Uploader::init();
        
        // Initialize download handling
        SFD_File_Downloader::init();
        
        // Initialize legacy file handler
        SFD_Legacy_File_Handler::init();
        
        // Initialize meta boxes
        SFD_Meta_Boxes::init();
    }
}