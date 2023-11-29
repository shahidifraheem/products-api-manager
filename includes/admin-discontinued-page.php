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

        <form method="post">
            <!-- Product API Url -->
            <div class="input-box">
                <h3><label for="product_api_url">Product API Url</label></h3>
                <input type="url" name="product_api_url" id="product_api_url" value="<?= esc_url(get_option('manage_product_api_url')); ?>" />
            </div>
            <h4>OR</h4>
            <!-- Product API Code -->
            <div class="input-box">
                <h3><label for="product_api_code">Paste the CSV Code</label></h3>
                <textarea name="product_api_code" id="product_api_code" value="<?= get_option('manage_product_api_code'); ?>" cols="30" rows="15"></textarea>
            </div>
            <br>
            <!-- Save Button -->
            <input type="submit" onclick="return window.confirm('It may take some time according the size of api, are you sure you want to update?')" name="save_products_manager_theme_options" class="button-primary" value="Update Api" />
        </form>
        <br>

        <h3 class="nav-tab-wrapper">
            <a href="#discontinued" class="nav-tab nav-tab-active" data-tab="tab">discontinued</a>
        </h3>

        <div class="tab-content" id="tab">
            <form method="post">
                <h4>
                    <label for="discontinued">Discontinued products in our Store:</label>
                </h4>
                <div id="discontinued-box">
                    <select name="discontinued[]" id="discontinued-api" multiple></select>
                </div>
                <br>
                <button name="save_discontinued_theme_options" onclick="return window.confirm('Are you sure you want to publish the selected products?')" class="button-primary">Add Missing Products</button>
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

        $csv_data = "ID,title,price\n";

        foreach ($products as $product) {
            $product_id = $product->ID;
            $product_title = $product->post_title;

            // Get product price (assuming it's stored as post meta with key 'price')
            $product_price = get_post_meta($product_id, '_price', true);

            // Add product ID, title, and price to CSV string
            $csv_data .= "$product_id,\"$product_title\",$product_price\n";
        }

        return $csv_data;
    }

    // Get products data as CSV format
    $csv_data = get_discontinued_product_data_as_csv();

    $api_url = esc_url(plugins_url() . "/products-manager/includes/apis/general-api.csv");
    $api_content = file_get_contents($api_url);

    // If file return empty content then override with the texarea content
    if ($api_content == "") {
        $api_content = get_option('manage_product_api_code');
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

                // Call the function with your CSV data
                const api_array = csvToObjectArray(csvData);
                console.log("Api Array: ", api_array)

                const products_array = csvToObjectArray(`<?= $csv_data ?>`);
                console.log("Product Array: ", products_array)

                // Function to find common sub-arrays based on a specific property
                function common_products(apiArray, productsArray, api_property, product_property) {
                    return apiArray.filter(apiObj =>
                        productsArray.some(productsObj => apiObj[api_property] === productsObj[product_property])
                    );
                }

                // Common Products from APi and Store - Available
                const available_common_products = common_products(api_array, products_array, 'title', 'title');
                const products_stock_out = available_common_products.filter(product => product['Quantity'] < 1);

                // Products out of stock in Live Api - Discontinued
                const discontinued = document.querySelector("#discontinued");
                discontinued.innerHTML = "";

                // Use a Set to store unique product titles
                const uniqueTitles = new Set();

                products_stock_out.forEach(product => {
                    // Check if the product title is not already in the set
                    if (!uniqueTitles.has(product.title)) {
                        // Add the product title to the set
                        uniqueTitles.add(product.title);

                        // Render the option
                        discontinued.innerHTML += `
                            <option value="${product.title}">${product.title}</option>
                        `;
                    }
                });
            });
        </script>
    <?php }
add_action('admin_head', 'fecth_api_discontinued_code'); ?>