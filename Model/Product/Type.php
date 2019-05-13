<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;

class Type extends AbstractType
{
    const TYPE_ID = 'warranty';

    /**
     * Check is virtual product
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    public function isVirtual($product)
    {
        return true;
    }

    /**
     * Check that product of this type has weight
     *
     * @return bool
     */
    public function hasWeight()
    {
        return false;
    }

    /**
     * Delete data specific for Warranty product type
     *
     * @param Product $product
     *
     * @return void
     */
    public function deleteTypeSpecificData(Product $product)
    {
    }
}
