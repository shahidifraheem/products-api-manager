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
function products_manager_panel_page()
{
?>
    <div class="wrap">
        <h1>Products Manager By API</h1>

        <form method="post" enctype="multipart/form-data">
            <!-- Product API Url -->
            <div class="input-box">
                <label for="product_api_url">Product API Url</label><br>
                <input type="url" name="product_api_url" id="product_api_url" style="width: 100%; max-width: 500px" value="<?= esc_url(get_option('manage_product_api_url')); ?>" />
            </div>
            <br>
            <!-- Save Button -->
            <input type="submit" onclick="return window.confirm('It may take some time according the size of api, are you sure you want to update?')" name="save_products_manager_theme_options" class="button-primary" value="Update Api" />
        </form>
        <br>

        <h3 class="nav-tab-wrapper">
            <a href="#available" class="nav-tab nav-tab-active" data-tab="tab1">Available</a>
            <a href="#missing" class="nav-tab" data-tab="tab2">Missing</a>
            <a href="#discontinued" class="nav-tab" data-tab="tab3">Discontinued</a>
        </h3>

        <div class="tab-content active" id="tab1">
            <h4>
                <label for="available">Available products in Api and Store:</label>
            </h4>
            <div id="available-box">
                <select name="available" id="available" multiple></select>
            </div>
        </div>

        <div class="tab-content" id="tab2">
            <form action="">
                <h4>
                    <label for="missing">Missing products in our Store:</label>
                </h4>
                <div id="missing-box">
                    <select name="missing" id="missing" multiple></select>
                </div>
                <br>
                <button class="button-primary">Add Missing Products</button>
            </form>
        </div>

        <div class="tab-content" id="tab3">
            <form action="">
                <h4>
                    <label for="discontinued">Out of Stock Products in Live:</label>
                </h4>
                <div id="discontinued-box">
                    <select name="discontinued" id="discontinued" multiple></select>
                </div>
                <br>
                <button class="button-primary">Also Update Store Products</button>
            </form>
        </div>
    <?php }


function fecth_api_manager_code()
{
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1, // Retrieve all products, you can adjust this number as needed
    );
    $products = get_posts($args);

    // echo $json_data;
    function get_product_titles_as_csv()
    {
        $args = array(
            'post_type'      => 'product', // Change to your custom post type or WooCommerce product type
            'posts_per_page' => -1
        );

        $products = get_posts($args);

        $csv_data = "title\n";

        foreach ($products as $product) {
            $product_title = $product->post_title;

            // Add product title to CSV string
            $csv_data .= "\"$product_title\"\n";
        }

        return $csv_data;
    }

    // Get product titles as CSV data
    $csv_data = get_product_titles_as_csv();


    $api_url = esc_url(plugins_url() . "/products-manager/includes/apis/general-api.csv");
    $api_content = file_get_contents($api_url);
    ?>
        <style>
            select {
                min-width: 600px;
                min-height: 500px !important;
            }

            .tab-content {
                display: none;
            }

            .tab-content.active {
                display: block;
            }
        </style>
        <script>
            // JavaScript code to fetch CSV data and convert it to an object
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

                // Replace 'your_csv_data' with the actual CSV data
                var csvData = `<?= $api_content ?>`;

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

                        return obj;
                    });

                    return result;
                }


                // Call the function with your CSV data
                const api_array = csvToObjectArray(csvData);

                for (let i = 0; i < api_array.length; i++) {
                    // api_array = api_array[i];
                }
                console.log(api_array)

                console.log("---------------- Divider ----------------")

                const products_array = csvToObjectArray(`<?= $csv_data ?>`);
                for (let i = 0; i < products_array.length; i++) {
                    // products_array = products_array[i];
                }
                console.log(products_array)

                // Function to find common sub-arrays based on a specific property
                function findCommonSubArrays(apiArray, productsArray, api_property, product_property) {
                    return apiArray.filter(apiObj =>
                        productsArray.some(productsObj => apiObj[api_property] === productsObj[product_property])
                    );
                }

                // Common Products - Available
                const available_products = findCommonSubArrays(api_array, products_array, 'title', 'title');
                console.log('Common Sub-Arrays:', available_products);

                const available = document.querySelector("#available");
                available.innerHTML = "";
                available_products.forEach(product => {
                    available.innerHTML += `
                        <option value="${generate_slug(product.title)}">${product.title}</option>
                `;
                });

                // Products only available in Live Api - Missing
                const missing_products = api_array.filter(apiItem =>
                    !products_array.some(productItem => productItem.title === apiItem.title)
                );

                const missing = document.querySelector("#missing");
                missing.innerHTML = "";
                missing_products.forEach(product => {
                    missing.innerHTML += `
                        <option value="${generate_slug(product.title)}">${product.title}</option>
                        `;
                });
                console.log('Items only in api_array:', missing_products);

                // Products out of stock in Live Api - Discontinued
                const products_stock_out = api_array.filter(product => product['Quantity'] < 1);

                const discontinued = document.querySelector("#discontinued");
                discontinued.innerHTML = "";
                products_stock_out.forEach(product => {
                    discontinued.innerHTML += `
                        <option value="${generate_slug(product.title)}">${product.title}</option>
                        `;
                });
                console.log('Products with In stock equal to 0:', products_stock_out);
            });
        </script>

        <script>
            jQuery(document).ready(function($) {
                // Handle tab clicks
                $('.nav-tab').on('click', function(e) {
                    e.preventDefault();

                    // Get the data-tab attribute value
                    var tabId = $(this).data('tab');

                    // Hide all tab contents
                    $('.tab-content').removeClass('active');

                    // Show the selected tab content
                    $('#' + tabId).addClass('active');

                    // Remove active class from all tabs
                    $('.nav-tab').removeClass('nav-tab-active');

                    // Add active class to the clicked tab
                    $(this).addClass('nav-tab-active');
                });
            });
        </script>
    <?php }
add_action('admin_head', 'fecth_api_manager_code');
    ?>