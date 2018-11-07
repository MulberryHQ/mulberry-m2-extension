<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Block\Cart\Item\Renderer;

use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Shopping cart item render block for warranty products.
 */
class Warranty extends Renderer implements IdentityInterface
{
    /**
     * Render quote item name rather than original product name
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->getItem()->getName();
    }

    /**
     * Return empty product URL for warranty product
     *
     * @return bool|string
     */
    public function getProductUrl()
    {
        return '';
    }
}
