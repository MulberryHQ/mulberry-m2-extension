# Mulberry Warranty Extension

The Mulberry Warranty extension allows the addition of an additional warranty product to an original Magento product.

## Installation

### Composer installation:

Run the following commands:

```
composer config repositories.getmulberry/mulberry-m2-extension git https://github.com/MulberryHQ/mulberry-m2-extension.git
composer require getmulberry/mulberry-m2-extension
bin/magento setup:upgrade
```

### Module uninstall:

To uninstall, please refer to the official Magento docs at [https://devdocs.magento.com/guides/v2.2/install-gde/install/cli/install-cli-uninstall-mods.html](https://devdocs.magento.com/guides/v2.2/install-gde/install/cli/install-cli-uninstall-mods.html)

The module uninstall script performs the following actions:

- Removes the module specific system config values from the `core_config_data` table.
- Removes the warranty product from the "apply_to" list for specific Magento attributes (price, cost, weight).
- Removes all Magento products with `type_id == warranty`. This automatically removes warranty products from active Magento quotes.

## Configuration

### Module Configuration

A merchant (admin user) can configure the following fields in Magento admin, which are required to initialize a Mulberry warranty iframe on the Product Details Page:

- **Enable Mulberry Warranty Block**
    - Enables/disables the Mulberry module.
- **Mulberry API URL**
    - Sets base URL used for API requests (e.g. `https://www.getmulberry.com`).
- **Mulberry Partner Base URL**
    - Sets the Mulberry Partner URL. This URL is used to perform backend API requests as well as to initialize the iframe on the Product Details Page (PDP). e.g `partner.getmulberry.com`.
- **Platform Domain Name**
    - Sets the merchant's domain name. If no value is set, the global value of `$_SERVER['SERVER_NAME']` is used.
- **Mulberry Retailer ID**
    - Sets the retailer ID generated in the Mulberry system.
- **Private Token**
    - Sets the Mulberry Private Token for merchant authorization, when sending API calls through the backend.
- **Public Token**
    - Sets the Mulberry Public Token for merchant authorization, when requesting warranty product information on the PDP.
- **Enable Post Purchase**
    - Enables/disables the Mulberry "Post Purchase" hook.

### Warranty Product Configuration

When the module is installed, it automatically creates

- A custom virtual product type called `Warranty Product`.
- A product placeholder that is used to store Mulberry warranty information during the customer journey.

When warranty information is retrieved from the Mulberry service, the product name and price are updated on-the-fly. These product placeholders can be found with the following SKUs:

- `mulberry-warranty-product`
- `mulberry-warranty-24-months`
- `mulberry-warranty-36-months`
- `mulberry-warranty-48-months`
- `mulberry-warranty-60-months`
- `mulberry-warranty-120-months`

To set a custom image for a warranty product, use the [default Magento product image functionality](https://docs.magento.com/m1/ce/user_guide/catalog/product-images.html).

**IMPORTANT!!!**

Please do **not** modify the SKU of the placeholder product. Otherwise the system won't be able to recognize and add a warranty product for the original Magento product.

## Technical Documentation

### Product Details Page
As soon as the DOM is fully loaded on the Product Details Page, the Mulberry iframe displaying warranty products is initialized.

### Magento event observers

In order to add a warranty product to the cart, as well as process it during the customer journey, the Mulberry module listens to the following Magento event observers:

- `checkout_cart_product_add_after` On this event, the module checks if the warranty product's hash has been passed as a form request. If so, a Magento warranty product placeholder is loaded using its SKU. Next, a REST API request is made to retrieve the warranty product's information (e.g. name, price, service_type, etc.). All of this data is stored under `warranty_information` of the particular quote item within the `quote_item_option` table.

- `sales_quote_item_set_product` On this event, the module updates the product name of the warranty product (quote item).

- `sales_order_place_after` On this event, the module runs the checkout success & post purchase hook. As soon as the order is placed and if the module is enabled, a corresponding record is added to the queue table and then sent to the Mulberry platform asynchronously.  Order sync is made only if the Magento order contains a warranty product.

- `order_cancel_after` On this event, the module checks if there's any warranty product available on the order. If so, it sends Mulberry cancel API request.

- `sales_order_place_before` On this event, we generate the unique order identifier aka UUID

### Quote item options modifications

In order to store a warranty product's data, the module uses the following custom product options:

- `warranty_information` This option contains a parsed API response about the warranty product added to the cart (name, price & other information).

- `additional_options` This option uses the default Magento functionality to display custom options applied to the product. This information is displayed on the shopping cart, checkout, and order pages. If there are warranty products, the module stores and displays the following information:

- `Service type`, e.g. "Accidental Damage Replacement".

- `Duration Months`, an integer value that specifies duration of the extended warranty for particular product in months (for example, "36").
