<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Mulberry\Warranty\Api\ItemUpdaterInterface as ItemUpdater;
use Mulberry\Warranty\Model\Product\Type as WarrantyProductType;

class SetWarrantyProductName implements ObserverInterface
{
    /**
     * @var RequestInterface $request
     */
    private $request;

    /**
     * @var ItemUpdater $warrantyItemUpdater
     */
    private $warrantyItemUpdater;

    /**
     * AddWarranty constructor.
     *
     * @param RequestInterface $request
     * @param ItemUpdater $itemUpdater
     */
    public function __construct(
        RequestInterface $request,
        ItemUpdater $itemUpdater
    ) {
        $this->request = $request;
        $this->warrantyItemUpdater = $itemUpdater;
    }

    /**
     * Set custom quote item name for warranty products
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /**
         * @var Quote\Item $quoteItem
         */
        $quoteItem = $observer->getEvent()->getQuoteItem();

        if ($quoteItem->getProductType() === WarrantyProductType::TYPE_ID) {
            $this->warrantyItemUpdater->updateWarrantyProductName($quoteItem);
        }
    }
}
