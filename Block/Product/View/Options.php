<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Block\Product\View;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\View\Options as CoreOptions;

class Options extends CoreOptions
{
    /**
     * Extend custom options with option SKU information
     *
     * @param Product\Option|Product\Option\Value $option
     *
     * @return array
     */
    protected function _getPriceConfiguration($option)
    {
        $data = parent::_getPriceConfiguration($option);

        if ($valueSku = $option->getSku()) {
            $data['value_sku'] = $valueSku;
        }

        return $data;
    }
}
