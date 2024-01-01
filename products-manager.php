<?php

/*
 * Plugin Name:       Products Manager
 * Description:       This plugin will handle the products through the apis
 * Version:           1.0
 * Author:            webcentral
 * Author URI:        https://webcentral.io
 * Update URI:        https://github.com/shahidifraheem/products-api-manager
 * Text Domain:       products-manager
 */

// Disable direct file access
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include the admin panel page
if (isset($_GET["page"]) && $_GET["page"] == "products-manager-panel") {
    require_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';
}

// Include the admin discontinued panel page
if (isset($_GET["page"]) && $_GET["page"] == "products-discontinued-panel") {
    require_once plugin_dir_path(__FILE__) . 'includes/admin-discontinued-page.php';
}

// Include the admin hooks
require_once plugin_dir_path(__FILE__) . 'includes/admin-hooks.php';

// Enqueue styles for the admin panel.
add_action('wp_enqueue_scripts', 'products_manager_enqueue_styles');

/**
 * Enqueues scripts and styles.
 *
 * @return void
 */
function products_manager_enqueue_styles()
{
    wp_enqueue_style('products-manager-css', plugin_dir_url(__FILE__) . 'assets/css/style.css');
}

/**
 * Show alert to install required plugin
 * 
 */
function show_admin_notices()
{
    $plugin_message = "";
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');

    // Install and activate WooCommerce
    if (!class_exists('WooCommerce')) {
        $plugin_message = '<div class="notice notice-error is-dismissible">Products Api Manager requires you to install and activate WooCommerce, <a style="display:inline-block;padding:5px; margin: 10px 0;" href="' . esc_url(admin_url('update.php?action=install-plugin&plugin=woocommerce&_wpnonce=' . wp_create_nonce('install-plugin_woocommerce'))) . '">download it from here</a>.</div>';
    }

    if ($plugin_message != "") {
        echo $plugin_message;
    }
}
add_action('admin_notices', 'show_admin_notices');


// Function to get MIME type based on file extension
function get_mime_type($filename)
{
    $file_info = wp_check_filetype($filename);

    // If wp_check_filetype returns a valid MIME type, use it
    if ($file_info['type']) {
        return $file_info['type'];
    } else {
        // Fallback to a default MIME type (you can adjust this based on your needs)
        return 'image/jpeg';
    }
}
