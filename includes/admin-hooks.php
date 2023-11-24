<?php

/**
 * Register Products panel page at the administrator level
 *
 * @return void
 */
function products_manager_panel_menu()
{
    add_menu_page('Products Manager Panel', 'Products Panel', 'manage_options', 'products-manager-panel', 'products_manager_panel_page', 'dashicons-products');
}
// Hook to add the admin page.
add_action('admin_menu', 'products_manager_panel_menu');

/**
 * Manage the products theme panel page data from the admin panel
 *
 * This will store the data into database after satitization and validation
 * 
 * @return void
 */
function products_manager_save_settings()
{
    if (isset($_POST['save_products_manager_theme_options']) && isset($_GET["page"]) && $_GET["page"] == "products-manager-panel") {

        // Products manager api validation
        if (isset($_POST['product_api_url'])) {
            $product_api_url_value = filter_var($_POST['product_api_url'], FILTER_SANITIZE_URL);

            $api_content = file_get_contents($product_api_url_value);

            // Get the absolute path to the plugin directory
            $plugin_dir = plugin_dir_path(__FILE__);

            // Specify the local file path within the plugin directory
            $local_api_path = $plugin_dir . "apis/general-api.csv";
            $local_api_content = file_get_contents($local_api_path);

            if ($api_content !== false) {
                // Update the content
                $updated_content = str_replace($local_api_content, $api_content, $api_content);

                // Write the updated content back to the file
                $result = file_put_contents($local_api_path, $updated_content);

                if ($result !== false) {
                    update_option('manage_product_api_url', $product_api_url_value);
                    echo '<script>alert("API updated successfully!")</script>';
                } else {
                    echo '<script>alert("Error writing to the file.")</script>';
                }
            }
        }
    }
}

add_action('admin_init', 'products_manager_save_settings');
