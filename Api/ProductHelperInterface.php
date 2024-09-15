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
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item;

interface ProductHelperInterface
{
    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getProductBreadcrumbs(ProductInterface $product): array;

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getGalleryImagesInfo(ProductInterface $product): array;

    /**
     * Retrieve product description
     *
     * @param ProductInterface $product
     *
     * @return string
     */
    public function getProductDescription(ProductInterface $product): string;
}
