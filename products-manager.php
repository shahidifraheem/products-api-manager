<?php

/*
 * Plugin Name:       Products Manager
 * Description:       This plugin will handle the products through the apis
 * Version:           1.0
 * Author:            Shahid Ifraheem
 * Author URI:        https://www.linkedin.com/in/shahid-ifraheem/
 * Text Domain:       products-manager
 */

// Disable direct file access
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include the admin panel page
require_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';

// Include the admin discontinued panel page
require_once plugin_dir_path(__FILE__) . 'includes/admin-discontinued-page.php';

// Include the admin hooks
require_once plugin_dir_path(__FILE__) . 'includes/admin-hooks.php';


// Enqueue styles for the admin panel.
// add_action('admin_enqueue_scripts', 'products_manager_enqueue_styles');

/**
 * Enqueues scripts and styles.
 *
 * @return void
 */
function products_manager_enqueue_styles()
{
    wp_enqueue_style('products-manager-admin-css', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_script('products-manager-index-js', plugin_dir_url(__FILE__) . 'assets/js/index.js', array('jquery'), '', true);
}
