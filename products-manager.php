<?php

/*
 * Plugin Name:       Products Manager
 * Description:       This plugin will handle the products through the apis
 * Version:           1.0
 * Author:            Shahid Ifraheem
 * Author URI:        https://www.linkedin.com/in/shahid-ifraheem
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
