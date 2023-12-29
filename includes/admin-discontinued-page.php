<?php
// Disable direct file access
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


/**
 * this is a admin page template file to controle the options and settings
 * 
 * @return PANEL_HTML_TEMPLATE
 */
function products_manager_discontinued_page()
{
?>
    <div class="wrap">
        <h1>Products Manager By API</h1>
        <h4 style="background: white; padding: 5px 10px; border-radius: 5px; display: inline-flex; gap:10px; align-items-center;"><span>Test Api Url: <span style="user-select: all;"><?= esc_url(plugins_url() . "/products-api-manager/test-apis/test-api.csv") ?></span></span> <a href="<?= esc_url(plugins_url() . "/products-api-manager/test-apis/test-api.csv") ?>" download>Download Test Api</a></h4>
        <form method="post">
            <!-- Product API Url -->
            <div class="input-box">
                <h3><label for="product_api_discontinued_url">Product API Url</label></h3>
                <input type="url" name="product_api_discontinued_url" id="product_api_discontinued_url" value="<?= esc_url(get_option('manage_product_api_discontinued_url')); ?>" />
            </div>
            <h4>OR</h4>
            <!-- Product API Code -->
            <div class="input-box">
                <h3><label for="product_api_discontinued_code">Paste the CSV Code</label></h3>
                <textarea name="product_api_discontinued_code" id="product_api_discontinued_code" value="<?= get_option('manage_product_api_discontinued_code'); ?>" cols="30" rows="15"></textarea>
            </div>
            <br>
            <!-- Save Button -->
            <input type="submit" onclick="return window.confirm('It may take some time according the size of api, are you sure you want to update?')" name="save_products_manager_discontinued_theme_options" class="button-primary" value="Update Api" />
        </form>
        <br>

        <h3 class="nav-tab-wrapper">
            <a href="#discontinued" class="nav-tab nav-tab-active" data-tab="tab">Discontinued</a>
        </h3>

        <div class="tab-content" id="tab">
            <form method="post">
                <h4>
                    <label for="discontinued">Discontinued products in our Store:</label>
                </h4>
                <div class="check-all">
                    <input type="checkbox" id="discontinued-select-all" class="select-all">
                    <label for="discontinued-select-all">Select All</label>
                </div>
                <hr>
                <div id="discontinued-checkboxes" class="checkbox-container">
                    <!-- Checkboxes rendering from jQuery -->
                </div>
                <br>
                <button name="save_discontinued_separate_theme_options" onclick="return window.confirm('Are you sure you want to continue as all products with same titles will be marked as out of stock?')" class="button-primary">Mark as Out of Stock</button>
            </form>
        </div>

        <style>
            /* Styling for Tabs */
            select:not(#price-increase),
            textarea {
                min-width: 600px;
                min-height: 500px !important;
            }

            input,
            textarea {
                width: 100%;
                max-width: 700px;
            }

            input[type="submit"] {
                width: auto;
            }
        </style>
    <?php }


/**
 * Embed JavaScript code inside in the head tag
 *
 * @return void
 */
function fecth_api_discontinued_code()
{
    function get_discontinued_product_data_as_csv()
    {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1
        );

        $products = get_posts($args);

        $csv_data = "ID,title,SKU,description,price\n";

        foreach ($products as $product) {
            $product_id = $product->ID;
            $product_title = $product->post_title;

            // Get product SKU (assuming it's stored as post meta with key '_sku')
            $product_sku = get_post_meta($product_id, '_sku', true);

            // Get product description (assuming it's stored as post meta with key '_product_description')
            $product_description = get_post_meta($product_id, '_product_description', true);

            // Get product price (assuming it's stored as post meta with key '_price')
            $product_price = get_post_meta($product_id, '_price', true);

            // Add product ID, title, SKU, description, and price to CSV string
            $csv_data .= "$product_id,\"$product_title\",\"$product_sku\",\"$product_description\",$product_price\n";
        }

        return $csv_data;
    }


    // Get products data as CSV format
    $csv_data = get_discontinued_product_data_as_csv();

    $api_url = esc_url(plugins_url() . "/products-api-manager/includes/apis/discontinued-api.csv");
    $api_content = file_get_contents($api_url);

    // If file return empty content then override with the texarea content
    if ($api_content == "") {
        $api_content = get_option('manage_product_api_discontinued_code');
    }
    ?>
        <script>
            // Manage tabs content
            document.addEventListener("DOMContentLoaded", function() {

                function generate_slug(title) {
                    // Replacing spaces with hyphens, removing special characters, and converting to lowercase
                    const slug = title
                        .replace(/[^\w\s-]/g, '') // Remove special characters
                        .trim()
                        .replace(/\s+/g, '-') // Replace spaces with hyphens
                        .toLowerCase();

                    return slug;
                }

                /**
                 * Double Quotes remover
                 * 
                 */
                function removeDoubleQuotes(input) {
                    if (input === undefined || input === null) {
                        return input;
                    } else {
                        // Use a regular expression to remove double quotes
                        return input.replace(/"/g, '');
                    }
                }

                // Store csv data from php variable to js variable
                const csvData = `<?= $api_content ?>`;

                // Function to convert CSV data to an array of objects
                function csvToObjectArray(csvData) {
                    // Split CSV data into rows
                    const rows = csvData.trim().split('\n');

                    // Extract headers from the first row, removing any surrounding quotes
                    const headers = rows[0].split(',').map(header => header.replace(/^"|"$/g, '').trim());

                    // Process each row and create an object for each
                    const result = rows.slice(1).map(row => {
                        // Split the row using a regular expression that handles commas inside quotes
                        const values = row.split(/,(?=(?:(?:[^"]*"){2})*[^"]*$)/).map(value => value.replace(/^"|"$/g, '').trim());
                        const obj = {};

                        headers.forEach((header, index) => {
                            obj[header] = values[index];
                        });

                        // Returning the object
                        return obj;
                    });

                    // Returning the result
                    return result;
                }

                // Api array
                const api_array = csvToObjectArray(csvData);
                console.log(api_array);

                // local products array
                const products_array = csvToObjectArray(`<?= $csv_data ?>`);

                console.log(products_array);
                // Function to find common sub-arrays based on a specific property
                function common_products(apiArray, productsArray, api_property, product_property) {
                    return apiArray.filter(apiObj =>
                        productsArray.some(productsObj => apiObj[api_property] === productsObj[product_property])
                    );
                }

                // Common Products from APi and Store - Available
                let available_common_products_title = common_products(api_array, products_array, 'title', 'title');
                console.log("available_common_products_title: ", available_common_products_title)

                let available_common_products_sku = common_products(api_array, products_array, 'SKU', 'SKU');
                console.log("available_common_products_sku: ", available_common_products_sku)

                let available_common_products_desc = common_products(api_array, products_array, 'description', 'description');
                console.log("available_common_products_desc: ", available_common_products_desc)

                let products_stock_out = available_common_products_title.filter(product => product['Quantity'] < 1);

                if (available_common_products_title && available_common_products_title.length > 0) {
                    console.log("Title")
                } else if (available_common_products_sku && available_common_products_sku.length > 0) {
                    console.log("SKU")
                } else {
                    console.log("Description")
                }

                // Products out of stock in Live Api - Discontinued
                const discontinued = document.querySelector("#discontinued-checkboxes");
                discontinued.innerHTML = "";

                // Use a Set to store unique product titles
                const uniqueTitles = new Set();

                products_stock_out.forEach(product => {

                    if (product.title) {
                        // Check if the product title is not already in the set
                        if (!uniqueTitles.has(product.title)) {
                            // Add the product title to the set
                            uniqueTitles.add(product.title);

                            // Render the option
                            discontinued.innerHTML += `
                            <div class="input-box">
                            <input type="checkbox" name="discontinued[]" id="discontinued-${generate_slug(product.title)}" value="::>${product.title != "" ? product.title : "null"}::>${product.description != "" ? product.description : "null"}::>${product.SKU != "" ? product.SKU : "null"}">
                            <label for="discontinued-${generate_slug(product.title)}">${product.title}</label>
                            </div>
                            `;
                        }
                    } else if (product.description) {
                        // Check if the product description is not already in the set
                        if (!uniqueTitles.has(product.description)) {
                            // Add the product description to the set
                            uniqueTitles.add(product.description);

                            // Render the option
                            discontinued.innerHTML += `
                            <div class="input-box">
                            <input type="checkbox" name="discontinued[]" id="discontinued-${generate_slug(product.title)}" value="::>${product.title != "" ? product.title : "null"}::>${product.description != "" ? product.description : "null"}::>${product.SKU != "" ? product.SKU : "null"}">
                            <label for="discontinued-${generate_slug(product.title)}">${product.title}</label>
                            </div>
                            `;
                        }
                    } else {
                        // Check if the product SKU is not already in the set
                        if (!uniqueTitles.has(product.SKU)) {
                            // Add the product SKU to the set
                            uniqueTitles.add(product.SKU);

                            // Render the option
                            discontinued.innerHTML += `
                            <div class="input-box">
                            <input type="checkbox" name="discontinued[]" id="discontinued-${generate_slug(product.title)}" value="::>${product.title != "" ? product.title : "null"}::>${product.description != "" ? product.description : "null"}::>${product.SKU != "" ? product.SKU : "null"}">
                            <label for="discontinued-${generate_slug(product.title)}">${product.title}</label>
                            </div>
                            `;
                        }
                    }
                });
            });

            jQuery(function($) {
                // When "Select All" checkbox is changed
                $(".select-all").change(function() {
                    // Check or uncheck all checkboxes based on the state of "Select All" checkbox
                    $(this).parents("form").find(".checkbox-container input[type='checkbox']").prop('checked', $(this).prop('checked'));
                });

                // When any checkbox inside the container is changed
                $(".checkbox-container").on('change', 'input[type="checkbox"]', function() {
                    // If all checkboxes are checked, check the "Select All" checkbox; otherwise, uncheck it
                    if ($(".checkbox-container input[type='checkbox']:checked").length === $(".checkbox-container input[type='checkbox']").length) {
                        $(this).parents("form").find(".select-all").prop('checked', true);
                    } else {
                        $(this).parents("form").find(".select-all").prop('checked', false);
                    }
                });
            })
        </script>
    <?php }
add_action('admin_head', 'fecth_api_discontinued_code'); ?>