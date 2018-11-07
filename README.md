# Mulberry Warranty Extension

Mulberry Warranty extension allows to add additional warranty product along with original Magento product.

## Installation

Composer installation:

```
1. Add module repository to your composer repositories
2. Run composer require scandiweb/module-mulberry
3. Run ./bin/magento setup:upgrade command
```

Manual installation:

```
1. Create Mulberry/Warranty folder in app/code directory within your Magento root folder
2. Extract module contents in that folder
3. Run ./bin/magento setup:upgrade command
```

Module uninstall:

In order to uninstall module, please check official Magento docs as a reference,
https://devdocs.magento.com/guides/v2.2/install-gde/install/cli/install-cli-uninstall-mods.html

Module uninstall script performs the following actions:
- Remove module specific system config values from `core_config_data` table
- Remove warranty product from "apply_to" list for specific Magento attributes (price, cost, weight)
- Remove all Magento products with type_id equal to `warranty`. It will automatically remove warranty products from active Magento quotes

## Configuration and Documentation

#### Module Configuration

Merchant (admin user) has an ability to configure the following fields in Magento admin, those are required in order to initialize Mulberry warranty iframe on Product Details Page:
- Enable Mulberry Warranty Block
    - This flag allows merchant to trigger enable/disable for Mulberry module functionality
- Mulberry API URL
    - This setting is used to specify base URL used for API requests (for example - https://staging.getmulberry.com)
- Mulberry Partner Base URL
    - This setting is used to specify Mulberry Partner URL. This URL is used to perform BackEnd API requests as well as initialize iframe on PDP
- Platform Domain Name
    - This setting is used to specify merchant's domain name, if no value is set, global value of $_SERVER['SERVER_NAME'] variable is used
- Mulberry Retailer ID
    - This setting is used to specify retailer ID generated in Mulberry system (for example - mulberry_placeholder)
- API Token
    - This setting is used to specify Mulberry API Token in order to authorize merchant, when requesting warranty product information on Product Details Page

#### Warranty Product Configuration

As soon as module is installed, it automatically creates custom virtual product type called "Warranty Product" as well as product placeholder that is used to store Mulberry warranty information during customer journey. As soon as warranty information is retrieved from Mulberry service, product name as well as price is updated on-the-fly. Such product can be found with the following SKU: `mulberry-warranty-product`.

In order to set custom image for a warranty product, it can be achieved using default Magento product image assignment functionality.

**IMPORTANT!!!**

**Do not modify SKU of this product, otherwise system won't be able to recognize and add warranty product along with an original Magento product.**

### Technical Documentation

#### Product Details Page
Mulberry iframe with warranty products is initialized as soon as DOM is fully loaded on Product Details Page.

#### Used Magento event observers

In order to add warranty product to the cart as well as process it during customer journey, module listens to the following Magento event observers:

- checkout_cart_product_add_after

At this step, module checks, if warranty product's hash has been passed as a form request. If yes, Magento warranty product placeholder is loaded using SKU. Afterwards, Rest API request is performed in order to retrieve warranty product's information (for example - name, price, service_type etc). All of this data is stored under `warranty_information` of particular quote item within `quote_item_option` table

- sales_quote_item_set_product

This event is used to update product name of warranty product (quote item)

- checkout_submit_all_after

This event is used to perform checkout success hook. As soon as order is placed, Magento performs API call in order to send payload to the Mulberry platform to notify that warranty product has been purchased. API call is performed only if Magento order contains warranty product

#### Quote item options modifications

In order to store the information of purchased warranty product, module uses the following custom product options:

- warranty_information

This option contains parsed API response about warranty product added to the cart (name, price & other information)

- additional_options

This option uses default Magento functionality to display custom options applied to the product. Information is displayed across shopping cart/checkout/order pages. In case with warranty products, module stores and displays the following information:

    - Service type, for example "Accidental Damage Replacement"
    - Duration Months, int value that specifies duration of the extended warranty for particular product (for example, "36")
