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
        $product_api_url_value = "";
        $product_api_code_value = "";

        // Api validation
        if (isset($_POST['product_api_url']) || isset($_POST['product_api_code'])) {
            // Check wether the api coming from url or textarea
            if ($_POST['product_api_url'] != "") {
                $product_api_url_value = filter_var($_POST['product_api_url'], FILTER_SANITIZE_URL);
                $api_content = file_get_contents($product_api_url_value);
            } else {
                $product_api_code_value = $_POST['product_api_code'];
                $api_content = $product_api_code_value;
            }

            // Get the absolute path to the plugin directory
            $plugin_dir = plugin_dir_path(__FILE__);

            // Specify the local file path within the plugin directory
            $local_api_path = $plugin_dir . "apis/general-api.csv";

            // Local api file content
            $local_api_content = file_get_contents($local_api_path);

            if ($api_content !== false) {
                // Update the content
                $updated_content = str_replace($local_api_content, $api_content, $api_content);

                // Write the updated content back to the file
                $result = file_put_contents($local_api_path, $updated_content);

                // Update content from Database if file updated successfully
                if ($result !== false) {
                    // Update database value based on the coming content type
                    if ($_POST['product_api_url'] != "") {
                        update_option('manage_product_api_url', $product_api_url_value);
                        update_option('manage_product_api_code', "");
                    } else {
                        update_option('manage_product_api_code', $product_api_code_value);
                        update_option('manage_product_api_url', "");
                    }
                    echo '<script>alert("API updated successfully!")</script>';
                } else {
                    echo '<script>alert("Failed to update the file.")</script>';
                }
            }
        }
    }
}

add_action('admin_init', 'products_manager_save_settings');

/**
 * Update products prices by 150%, 200%, 300%
 *
 * @return void
 */
function update_product_prices()
{
    if (isset($_POST['save_price_increase_theme_options']) && isset($_GET["page"]) && $_GET["page"] == "products-manager-panel") {

        $available_products = $_POST['available'];
        $price_increase = floatval(sanitize_text_field($_POST['price-increase']));

        // Loop through products
        if (!empty($available_products)) {
            foreach ($available_products as $available_id) {

                // Get current price
                $current_price = get_post_meta($available_id, '_price', true);

                // Update price if price is valid 
                if ($current_price != "") {
                    // Update price by 150-300%
                    $new_price = $current_price * $price_increase;

                    // Update post price with the new price
                    update_post_meta($available_id, '_price', $new_price);
                }
            }
        }
    }
}

add_action('admin_init', 'update_product_prices');

/**
 * Update discontinuted selected products status to draft
 *
 * @return void
 */
function update_discontinued_products()
{
    if (isset($_POST['save_discontinued_theme_options']) && isset($_GET["page"]) && $_GET["page"] == "products-manager-panel") {

        $discontinued_products = $_POST['discontinued'];

        // Loop through selected products
        if (!empty($discontinued_products)) {

            foreach ($discontinued_products as $product_title) {
                global $wpdb;

                // Update the post status with custom query
                $query = $wpdb->prepare(
                    "UPDATE {$wpdb->posts}
                    SET post_status = %s
                    WHERE post_title = %s",
                    "draft",
                    $product_title
                );

                $wpdb->query($query);
            }
        }
    }
}

add_action('admin_init', 'update_discontinued_products');
