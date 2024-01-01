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
        <h4 style="background: white; padding: 5px 10px; border-radius: 5px; display: inline-flex; gap:10px; align-items-center;"><span>Test Api Url: <span style="user-select: all;"><?= esc_url(plugins_url() . "/products-api-manager/test-apis/test-api.csv") ?></span></span> <a href="<?= esc_url(plugins_url() . "/products-api-manager/test-apis/test-api.csv") ?>" download>Download Test Api</a></h4>

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
            <a href="#available" class="nav-tab nav-tab-active" data-tab="tab1">Available</a>
            <a href="#missing" class="nav-tab" data-tab="tab2">Missing</a>
            <a href="#discontinued" class="nav-tab" data-tab="tab3">Discontinued</a>
        </h3>

        <div class="tab-content active" id="tab1">
            <form method="post">
                <h4 style="margin-bottom: 5px;">
                    Available products in Api and Store:
                </h4>
                <div class="check-all">
                    <input type="checkbox" id="available-select-all" class="select-all">
                    <label for="available-select-all">Select All</label>
                </div>
                <hr>
                <div id="available-checkboxes" class="checkbox-container">
                    <!-- Checkboxes rendering from jQuery -->
                </div>
                <div id="prices-box">
                    <h4 style="margin-bottom: 5px;">
                        <label for="price-increase">Increase available products price by:</label>
                    </h4>
                    <select name="price-increase" id="price-increase" width="100px" disabled>
                        <option value="">Select Price</option>
                        <option value="1.5">150%</option>
                        <option value="2">200%</option>
                        <option value="3">300%</option>
                    </select>
                </div>
                <div style="display: flex; gap: 20px;">
                    <div class="input-box">
                        <h4 style="margin-bottom: 5px;">
                            <label for="custom-price">Custom Price</label>
                        </h4>
                        <input type="text" id="custom-price" name="custom-price" placeholder="Enter custom price" width="100px" disabled>
                    </div>
                    <div class="input-box">
                        <h4 style="margin-bottom: 5px;">
                            <label for="threshold-price">Threshold Price</label>
                        </h4>
                        <input type="text" id="threshold-price" name="threshold-price" placeholder="Enter max increase price" width="100px" disabled>
                    </div>
                </div>
                <br>
                <button onclick="return window.confirm('Are you sure you want to increase the prices of selected products?')" name="save_price_increase_theme_options" class="button-primary">Update Prices</button>
                <button onclick="return window.confirm('Are you sure you want to continue as all products with same titles will be marked as out of stock?')" name="save_discontinued_theme_options" id="mark_out_of_stock" class="button-primary">Mark as Out of Stock</button>
            </form>
        </div>

        <div class="tab-content" id="tab2">
            <form method="post">
                <h4 style="margin-bottom: 5px;">
                    <label for="missing">Missing products in our Store:</label>
                </h4>
                <div class="check-all">
                    <input type="checkbox" id="missing-select-all" class="select-all">
                    <label for="missing-select-all">Select All</label>
                </div>
                <hr>
                <div id="missing-checkboxes" class="checkbox-container">
                    <!-- Checkboxes rendering from jQuery -->
                </div>
                <br>
                <button name="save_missing_theme_options" onclick="return window.confirm('Are you sure you want to publish the selected products?')" class="button-primary">Add Missing Products</button>
            </form>
        </div>

        <div class="tab-content" id="tab3">
            <form method="post">
                <h4 style="margin-bottom: 5px;">
                    <label for="discontinued">Out of Stock Products in Live:</label>
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
                <button onclick="return window.confirm('Are you sure you want to continue as all products with same titles will be marked as out of stock?')" name="save_discontinued_theme_options" class="button-primary">Mark as Out of Stock</button>
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

            .tab-content {
                display: none;
            }

            .tab-content.active {
                display: block;
            }
        </style>
    <?php }


/**
 * Embed JavaScript code inside in the head tag
 *
 * @return void
 */
function fecth_api_manager_code()
{
    function get_product_data_as_csv()
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
    $csv_data = get_product_data_as_csv();

    $api_url = esc_url(plugins_url() . "/products-api-manager/includes/apis/general-api.csv");

    // Initialize cURL session
    $ch = curl_init($api_url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL session and get the API content
    $api_content = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo '<script>alert("' . curl_error($ch) . 'Error fetching content from the API.")</script>';
    }

    // Close cURL session
    curl_close($ch);


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

                // Call the function with your CSV data
                const api_array = csvToObjectArray(csvData);

                const products_array = csvToObjectArray(`<?= $csv_data ?>`);

                // Function to find common sub-arrays based on a specific property
                function common_products(apiArray, productsArray, api_property, product_property) {
                    return apiArray.filter(apiObj =>
                        productsArray.some(productsObj => apiObj[api_property] === productsObj[product_property])
                    );
                }

                // Common Products from APi and Store - Available
                const available_products = common_products(products_array, api_array, 'title', 'title');

                // Function to add 'brand' property from api_array to available_products
                function mergeBrandProperty(availableProducts, apiArray) {
                    return availableProducts.map(product => {
                        const correspondingApiProduct = apiArray.find(apiProduct => apiProduct.title === product.title);

                        // If a corresponding product is found, add the 'brand' property
                        if (correspondingApiProduct) {
                            return {
                                ...product,
                                brand: correspondingApiProduct.brand
                            };
                        }

                        // If no corresponding product is found, return the original product
                        return product;
                    });
                }

                // Merge the 'brand' property
                const mergedProducts = mergeBrandProperty(available_products, api_array);

                const available = document.querySelector("#available-checkboxes");
                available.innerHTML = "";
                mergedProducts.forEach(product => {
                    available.innerHTML += `
                    <div class="input-box">
                        <input type="checkbox" name="available[]" id="available-${product.ID}" value="${product.ID}" data-price="${product.price}" data-title="${product.title}">
                        <label for="available-${product.ID}">${product.title} -&gt; ${product.price}  -&gt; ${product.brand}</label>
                    </div>
                `;
                });

                // Products only available in Live Api - Missing
                const missing_products = api_array.filter(apiItem =>
                    !products_array.some(productItem => productItem.title === apiItem.title)
                );

                const missing = document.querySelector("#missing-checkboxes");
                missing.innerHTML = "";
                missing_products.forEach(product => {
                    missing.innerHTML += `
                    <div class="input-box">
                        <input type="checkbox" name="missing[]" id="missing-${generate_slug(product.title)}" value="::>${product.title != "" ? removeDoubleQuotes(product.title) : "null"}::>${product.description != "" ? removeDoubleQuotes(product.description) : "null"}::>${product['Product Category'] != "" ? removeDoubleQuotes(product['Product Category']) : "null"}::>${product.price != "" ? product.price : "null"}::>${product.sale_price != "" ? product.sale_price : "null"}::>${product.Quantity != "" ? product.Quantity : "null"}::>${product.SKU != "" ? product.SKU : "null"}::>${product.size != "" ? product.size : "null"}::>${product.color != "" ? product.color : "null"}::>${product.brand != "" ? product.brand : "null"}::>${product.UPC != "" ? product.UPC : "null"}::>${product.shipping_weight != "" ? product.shipping_weight : "null"}::>${product.shipping_height != "" ? product.shipping_height : "null"}::>${product.shipping_length != "" ? product.shipping_length : "null"}::>${product.shipping_width != "" ? product.shipping_width : "null"}::>${product.image_link != "" ? product.image_link : "null"}" data-price="${product.price}" data-title="${product.title}">
                        <label for="missing-${generate_slug(product.title)}">${product.title} -&gt; ${product.price} -&gt;  ${product.brand}</label>
                    </div>`;
                });

                // Common Products from APi and Store - Available
                const available_common_products = common_products(api_array, products_array, 'title', 'title');
                const products_stock_out = available_common_products.filter(product => product['Quantity'] < 1);

                // Products out of stock in Live Api - Discontinued
                const discontinued = document.querySelector("#discontinued-checkboxes");
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
                        <div class="input-box">
                            <input type="checkbox" name="discontinued[]" id="discontinued-${generate_slug(product.title)}" value="${removeDoubleQuotes(product.title)}">
                            <label for="discontinued-${generate_slug(product.title)}">${product.title}</label>
                        </div>`;
                    }
                });
            });
        </script>

        <script>
            // Manage tabs navigation
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


                $("#mark_out_of_stock").click(function() {
                    $("#available-checkboxes input:checked").each(function() {
                        $(this).val($(this).data("title"))
                    })
                })

                $("#tab1 select, #tab1 input").on("change", function() {
                    // Check if #price-increase is selected or blank
                    let isPriceIncreaseSelected = $("#price-increase").val() === null || $("#price-increase").val() === "";
                    let availableSelected = $("#available").val() === null || $("#available").val() === "";

                    // Update the #threshold-price element's disabled attribute
                    $("#threshold-price").prop("disabled", isPriceIncreaseSelected);

                    $("#price-increase").prop("disabled", availableSelected);
                    $("#custom-price").prop("disabled", availableSelected);

                    // Iterate over each checked option inside #available
                    $("#available-checkboxes input:checked").each(function() {
                        // Get the value of the data-price attribute
                        let dataPrice = $(this).data("price");
                        let priceIncrease = $("#price-increase").val();
                        let thresholdField = $("#threshold-price");
                        let thresholdPrice = $("#threshold-price").val();

                        // Check if dataPrice is already a number (integer or float)
                        if (typeof dataPrice !== 'number') {
                            // Extract the numeric part from the data-price attribute
                            let numericPart = parseFloat(dataPrice.replace(/[^\d.]/g, ''));

                            // Convert the numeric part to a floating-point number
                            let priceValue = parseFloat(numericPart);

                            // Price increase by %
                            if (priceIncrease != "") {
                                let increasePrice = priceIncrease * priceValue;

                                if (thresholdPrice != "") {
                                    if (increasePrice > thresholdPrice) {
                                        if (!window.confirm($(this).text() + " increased price (" + increasePrice + ") greater than the threshold price (" + thresholdPrice + "), are you still want to continue?")) {
                                            thresholdField.val("")
                                        }
                                    }
                                }

                                // Output the result for each option
                                console.log("Option Value: " + $(this).val() + ", Converted Price: " + priceValue + " , Increased Price: " + increasePrice);
                            }
                            // Output the result for each option
                            console.log("Option Value: " + $(this).val() + ", Converted Price: " + priceValue);
                        }
                    });
                });
            })
        </script>
    <?php }
add_action('admin_head', 'fecth_api_manager_code'); ?>