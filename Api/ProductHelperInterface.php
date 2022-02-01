<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
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
     * @return mixed
     */
    public function getProductBreadcrumbs(ProductInterface $product);

    /**
     * @param ProductInterface $product
     * @return mixed
     */
    public function getGalleryImagesInfo(ProductInterface $product);
}
