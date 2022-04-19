Mulberry_Warranty changelog
========================

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
