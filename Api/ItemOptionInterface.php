<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Api;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item;

interface ItemOptionInterface
{
    /**
     * Retrieve warranty product option information for the existing quote item
     *
     * @param Item $quoteItem
     *
     * @return DataObject
     */
    public function getWarrantyOption(Item $quoteItem): DataObject;

    /**
     * Prepare warranty specific option information
     *
     * @param Item $originalQuoteItem
     * @param string $warrantyHash
     *
     * @return array
     */
    public function prepareWarrantyOption(Item $originalQuoteItem, string $warrantyHash): array;

    /**
     * Prepare warranty product information
     *
     * @param $warrantyHash
     *
     * @return array
     */
    public function prepareWarrantyInformation($warrantyHash): array;
}
