<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\CustomerData;

use Magento\Checkout\CustomerData\DefaultItem;

/**
 * Warranty item
 */
class WarrantyItem extends DefaultItem
{
    /**
     * {@inheritdoc}
     */
    protected function doGetItemData()
    {
        /**
         * Render quote item name rather than original product name
         */
        $data = parent::doGetItemData();

        if (isset($data['product_name'])) {
            $data['product_name'] = $this->item->getName();
        }

        if (isset($data['product_url'])) {
            $data['product_url'] = '';
        }

        if (isset($data['is_visible_in_site_visibility'])) {
            $data['is_visible_in_site_visibility'] = false;
        }

        return $data;
    }
}
