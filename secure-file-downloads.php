<?php
/**
 * Plugin Name: Secure File Downloads
 * Description: Manage secure file downloads with login requirements
 * Version: 1.0.0
 * Author: Your Name
 */

// Prevent direct script access
if (!defined('ABSPATH')) {
    die('No direct script access allowed');
}

// Initialize the plugin
require_once plugin_dir_path(__FILE__) . 'includes/init.php';