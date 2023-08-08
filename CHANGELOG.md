Mulberry_Warranty changelog
========================

2.0.0:
- Increased number of orders to process during cron run
-  Removed the outdated crawler command, added deprecation message
- Separated   sync order & sync cart CLI commands
- Orders are now synced using increment_id instead of entity_id  for better usability
- Changed event observer for the orders to cover admin & phone orders
- Added logic to be able  to force-add  records to the queue if needed outside the original module
- Made  getWarrantyPlaceholderProduct function public to allow updating  Mulberry placeholder product SKUs  via plugin
- Minor code cleanup

1.6.4:
- Increased frequency for the post purchase orders
- Fixed issue when incorrect order items were sent via post purchase/sendcart hook
- Fixed return type for the CLI sync commands

1.6.0:
- Added event to perform partial refund aka creditmemo

1.5.2:
- Fixed order sync command through the cron task
- Change data type of entity_id to allow for more than ~65K records to be inserted

1.5.0:
- Added force logging functionality for the Mulberry API calls
- Added separate CLI command to be able to force sync order & cart hooks using the Magento order_id param

1.4.1:
- Added CSP whitelist records for Mulberry API

1.4.0:
- Added logic to init inline/modal offers when there were no offers for the initial product selection
- Update frontend logic to use new SDK modal/inline offer checks

1.3.4:
- Added missing "self" declaration for the PDP component

1.3.3:
- Added default array declaration for post purchase hook

1.3.2:
- Reset warranty selection on configuration chang

1.3.1:
- Fixed issue when empty Mulberry container was adding redundant space for the "actions" CSS class
- Added dependency, so the "post-purchase" items are synced only if the respective order status sync is "synced" or "skipped"
- Removed "Product URL" parameter for the warranty product cart item renderer

1.3.0:
- Changed system config field mapping
- Modified Mulberry breadcrumbs rendering logic
- Added breadcrumbs & images to the post-purchase call
- Fixed add-to-cart messages
- Added validation for the warranty item before it's added to the shopping cart to prevent use cases when the Mulberry warranty product was added directly without API

1.2.5:
- Use order increment_id instead of order_identifier in send cart hook

1.2.4:
- Added "warranty_offer_id" to the request payload

1.2.3:
- Fixed incorrect warranty observer message logging issue

1.2.2:
- Added environment emulation
- Added timeout to Http client
- Updated messaging whenever warranty product add to cart is failing

1.2.1:
- Fixed issue when the warranty offer update didn't trigger if there was only inline & modal option enabled
- Fixed issue when the warranty offer was not added to the shopping cart, if the final SKU is too long

1.2.0:
- Added ability to crawl catalog and collect following product data: title, description, price, url, image urls, category urls
- Added ability to download generated json

1.1.0:
- Fixed "Undefined index" issue when adding product to the shopping cart, because the response payload is now different
- Added functionality to send order + post purchase (cart) actions async via cronjob
- Added custom Mulberry logger

1.0.2:
- Fixed content parse for the product "description" field
- Added product URL, breadcrumbs & images to the window.mulberryProductData.product variable
- Added "modal" check in the add-to-cart mixin
- "Add to cart" rework for the modal window
- Fixed SKU selection when the configurable swatches are used

1.0.1:
- Fixed typo in README installation instructions

1.0.0:
- Added order UUID generation functionality
- Added Magento 2.3 compatibility for get_personalized_warranty controller action
- Added SendCart functionality (Post Purchase)
- Restored FE user journey as per the latest API changes
- Fixed Warranty Registration hook
- Fixed Order Cancellation hook
- Extended order grid with order UUID
- Extended order item section with warranty information
- Separated warranty product placeholders
- FE user journey updates based on API changes
- Added public token config field
- API payload adjustments

0.1.0:
- Initial Magento functionality
