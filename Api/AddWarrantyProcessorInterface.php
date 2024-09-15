<?php
declare(strict_types=1);
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2024 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Api\Data\CartItemInterface;

interface AddWarrantyProcessorInterface
{
    /**
     * Process adding warranty product to the shopping cart
     *
     * @param CartItemInterface $quoteItem
     * @param array $requestParams
     *
     * @return ?CartItemInterface
     */
    public function execute(CartItemInterface $quoteItem, array $requestParams = []): ?CartItemInterface;

    /**
     * Retrieve a warranty product placeholder
     *
     * @param array $warrantyOptions
     *
     * @return ProductInterface
     */
    public function getWarrantyPlaceholderProduct(array $warrantyOptions = []): ProductInterface;

    /**
     * Perform data validation
     *
     * @param CartItemInterface $quoteItem
     * @param array $requestParams
     *
     * @return bool
     */
    public function validate(CartItemInterface $quoteItem, array $requestParams = []): bool;

    /**
     * Check whether quote item is eligible for the warranty
     *
     * @param CartItemInterface $quoteItem
     *
     * @return bool
     */
    public function isProductTypeAllowed(CartItemInterface $quoteItem): bool;
}
