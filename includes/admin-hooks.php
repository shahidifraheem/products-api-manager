<?php

/**
 * Register Products panel page at the administrator level
 *
 * @return void
 */
function products_manager_panel_menu()
{
    add_menu_page(
        'Products Manager Panel',
        'Products Panel',
        'manage_options',
        'products-manager-panel',
        'products_manager_panel_page',
        'dashicons-products'
    );

    // Add Discontinued Api
    add_submenu_page(
        'products-manager-panel',  // Parent menu slug
        'Discontinued Panel',            // Page title
        'Discontinued Panel',                 // Menu title
        'manage_options',
        'products-discontinued-panel',   // discontinued slug
        'products_manager_discontinued_page'
    );
}

// Hook to add the admin page and submenu
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

    // Product api separate options in discontinued
    if (isset($_POST['save_products_manager_discontinued_theme_options']) && isset($_GET["page"]) && $_GET["page"] == "products-discontinued-panel") {
        $product_api_discontinued_url_value = "";
        $product_api_discontinued_code_value = "";

        // Api validation
        if (isset($_POST['product_api_discontinued_url']) || isset($_POST['product_api_discontinued_code'])) {
            // Check wether the api coming from url or textarea
            if ($_POST['product_api_discontinued_url'] != "") {
                $product_api_discontinued_url_value = filter_var($_POST['product_api_discontinued_url'], FILTER_SANITIZE_URL);
                $api_content = file_get_contents($product_api_discontinued_url_value);
            } else {
                $product_api_discontinued_code_value = $_POST['product_api_discontinued_code'];
                $api_content = $product_api_discontinued_code_value;
            }

            // Get the absolute path to the plugin directory
            $plugin_dir = plugin_dir_path(__FILE__);

            // Specify the local file path within the plugin directory
            $local_api_path = $plugin_dir . "apis/discontinued-api.csv";

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
                    if ($_POST['product_api_discontinued_url'] != "") {
                        update_option('manage_product_api_discontinued_url', $product_api_discontinued_url_value);
                        update_option('manage_product_api_discontinued_code', "");
                    } else {
                        update_option('manage_product_api_discontinued_code', $product_api_discontinued_code_value);
                        update_option('manage_product_api_discontinued_url', "");
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

        // Check the discontinued is empty or not
        if (!empty($discontinued_products)) {
            // Loop through the discontinued products to update the titles
            foreach ($discontinued_products as $product_title) {

                // Query for the product by title
                $product_query = new WP_Query(array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'title' => $product_title,
                ));

                // Check if the product is found
                if ($product_query->have_posts()) {
                    while ($product_query->have_posts()) {
                        $product_query->the_post();

                        // Update the product stock status to 'outofstock'
                        update_post_meta(get_the_ID(), '_stock_status', 'outofstock');

                        // Update the product quantity to 0
                        update_post_meta(get_the_ID(), '_stock', 0);

                        echo '<script>alert("' . $product_title . ' marked as out of stock!")</script>';
                    }
                }
            }
        }
    }
}
add_action('admin_init', 'update_discontinued_products');

/**
 * Update discontinuted selected products status to draft
 * 
 * @return void
 */
function add_missing_products()
{
    if (isset($_POST['save_missing_theme_options']) && isset($_GET["page"]) && $_GET["page"] == "products-manager-panel") {

        $missing_products = $_POST['missing'];

        // Check the missing is empty or not
        if (!empty($missing_products)) {

            // Loop through the missing products
            foreach ($missing_products as $product_api_data) {
                // Split the string into an array using the comma as the delimiter
                $product_array = explode('::>', $product_api_data);

                // Insert product if title is not empty
                if ($product_array[1] != "null") {
                    $product_data = array(
                        'post_type' => 'product',
                        'post_title' => $product_array[1],
                        'post_content' => '',
                        'post_status' => 'publish',
                    );

                    $product_id = wp_insert_post($product_data);

                    if ($product_array[16] != "") {
                        $image_url = filter_var($product_array[16], FILTER_SANITIZE_URL);

                        // Download the image
                        $response = wp_remote_get($image_url);

                        // Check if the request was successful
                        if (!is_wp_error($response)) {
                            $response_code = wp_remote_retrieve_response_code($response);

                            // Check if the response code is 200 OK
                            if ($response_code === 200) {
                                // Get the image body
                                $image_data = wp_remote_retrieve_body($response);

                                // Get the filename from the URL
                                $filename = basename($image_url);

                                // Upload the image to the media library
                                $upload = wp_upload_bits($filename, null, $image_data);

                                // Check if the upload was successful
                                if (!$upload['error']) {
                                    // Get the uploaded image path
                                    $uploaded_image_path = $upload['file'];

                                    // Set the uploaded image as the featured image for the product
                                    $attachment_id = wp_insert_attachment(array(
                                        'post_title' => $filename,
                                        'post_mime_type' => 'image/jpeg', // Change this based on the image type
                                        'post_status' => 'inherit',
                                    ), $uploaded_image_path, $product_id);

                                    // Set the featured image
                                    set_post_thumbnail($product_id, $attachment_id);
                                } else {
                                    echo '<div style="float: right;">Failed to upload image for ' . $product_array[1] . '. Error: ' . $upload['error'] . '</div><br>';
                                }
                            } else {
                                echo '<div style="float: right;">Invalid image response code for ' . $product_array[1] . ': ' . $response_code . '</div><br>';
                            }
                        } else {
                            // Print WP_Error message
                            echo '<div style="float: right;">Invalid image response for ' . $product_array[1] . '. Error: ' . $response->get_error_message() . '</div><br>';
                        }
                    }

                    if (!is_wp_error($product_id)) {

                        // Set regular price
                        if ($product_array[4] != "null") {
                            update_post_meta($product_id, '_regular_price', $product_array[4]);
                            update_post_meta($product_id, '_price', $product_array[4]); // Set the regular price as the default price
                        }

                        // Set sale price
                        if ($product_array[5] != "null") {
                            update_post_meta($product_id, '_sale_price', $product_array[5]);
                        }

                        // Set SKU
                        if ($product_array[7] != "null") {
                            update_post_meta($product_id, '_sku', $product_array[7]);
                        }

                        // Set weight, length, width, height
                        if ($product_array[12] != "null") {
                            update_post_meta($product_id, '_weight', $product_array[12]);
                        }
                        if ($product_array[15] != "null") {
                            update_post_meta($product_id, '_length', $product_array[15]);
                        }
                        if ($product_array[14] != "null") {
                            update_post_meta($product_id, '_width', $product_array[14]);
                        }
                        if ($product_array[13] != "null") {
                            update_post_meta($product_id, '_height', $product_array[13]);
                        }

                        // Set product short description
                        if ($product_array[2] != "null") {
                            // Update post excerpt (short description)
                            wp_update_post(array('ID' => $product_id, 'post_excerpt' => $product_array[2] != "null" ? $product_array[2] : ""));
                        }

                        // Set category
                        if ($product_array[3] != "null") {
                            if (str_contains($product_array[3], ">")) {
                                $categories = explode('>', $product_array[3]); // Assuming categories are separated by >
                                // Loop through each part and set it as a category
                                foreach ($categories as $category) {
                                    wp_set_object_terms($product_id, $category, 'product_cat', true);
                                }
                            } else {
                                wp_set_object_terms($product_id, $product_array[3], 'product_cat');
                            }
                        }

                        // Set tags
                        if ($product_array[8] != "null") { // Assuming tags are in $product_array[8]
                            $tags = explode(',', $product_array[8]); // Assuming tags are comma-separated
                            wp_set_object_terms($product_id, $tags, 'product_tag');
                        }

                        echo '<script>alert("' . $product_array[1] . ' created and published successfully!")</script>';
                    } else {
                        echo '<script>alert("' . $product_array[1] . ' Error creating/publishing product: ' . $product_id->get_error_message() . ')</script>';
                    }
                }
            }
        }
    }
}
add_action('admin_init', 'add_missing_products');



/**
 * Update discontinuted selected products status to draft
 *
 * @return void
 */
function update_discontinued_separate_products()
{
    if (isset($_POST['save_discontinued_theme_options']) && isset($_GET["page"]) && $_GET["page"] == "products-manager-panel") {

        $discontinued_products = $_POST['discontinued'];

        // Check the discontinued is empty or not
        if (!empty($discontinued_products)) {
            // Loop through the discontinued products to update the titles
            foreach ($discontinued_products as $product_title) {

                // Query for the product by title
                $product_query = new WP_Query(array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'title' => $product_title,
                ));

                // Check if the product is found
                if ($product_query->have_posts()) {
                    while ($product_query->have_posts()) {
                        $product_query->the_post();

                        // Update the product stock status to 'outofstock'
                        update_post_meta(get_the_ID(), '_stock_status', 'outofstock');

                        // Optionally, update the product quantity to 0
                        update_post_meta(get_the_ID(), '_stock', 0);

                        echo '<script>alert("' . $product_title . ' marked as out of stock!")</script>';
                    }
                }
            }
        }
    }
}
add_action('admin_init', 'update_discontinued_separate_products');
