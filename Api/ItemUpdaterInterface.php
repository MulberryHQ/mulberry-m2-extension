<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Api;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item;

interface ItemUpdaterInterface
{
    /**
     * Set custom quote item name for warranty product
     *
     * @param Item $quoteItem
     *
     * @return Item
     */
    public function updateWarrantyProductName(Item $quoteItem): Item;

    /**
     * Assign custom price to warranty product
     *
     * @param Item $warrantyQuoteItem
     * @param array $options
     *
     * @return Item
     */
    public function setCustomWarrantyItemPrice(Item $warrantyQuoteItem, array $options = []): Item;

    /**
     * Add warranty specific option information to Magento product,
     * should be executed, before calling addProduct method while adding warranty product to cart
     *
     * @param Product $warrantyProduct
     * @param array $options
     *
     * @return Product
     */
    public function addWarrantyItemOption(Product $warrantyProduct, array $options = []): Product;

    /**
     * Add some of the attributes as additional options in order to show them to the customer on the FE,
     * for example, warranty duration & service type
     *
     * @param Product $warrantyProduct
     * @param array $options
     *
     * @return Product
     */
    public function addAdditionalOptions(Product $warrantyProduct, array $options = []): Product;
}
