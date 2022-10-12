<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Mulberry\Warranty\Api\Config\HelperInterface;
use Mulberry\Warranty\Api\Rest\CancelOrderServiceInterface;
use Mulberry\Warranty\Model\Product\Type;

class RefundOrder implements ObserverInterface
{
    /**
     * @var HelperInterface $configHelper
     */
    private $configHelper;

    /**
     * @var CancelOrderServiceInterface $orderCancelService
     */
    private $orderCancelService;

    /**
     * SendOrder constructor.
     *
     * @param HelperInterface $configHelper
     * @param CancelOrderServiceInterface $orderCancelService
     */
    public function __construct(HelperInterface $configHelper, CancelOrderServiceInterface $orderCancelService)
    {
        $this->configHelper = $configHelper;
        $this->orderCancelService = $orderCancelService;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->configHelper->isActive()) {
            return;
        }

        $creditMemo = $observer->getEvent()->getCreditmemo();
        $order = $creditMemo->getOrder();
        $itemsToRefund = [];
        foreach ($creditMemo->getAllItems() as $item) {
            if ($item->getOrderItem()->getProductType() == Type::TYPE_ID) {
                $itemsToRefund[] = $item->getOrderItem();
            }
        }

        if (!empty($itemsToRefund)) {
            $this->orderCancelService->cancelOrder($order, $itemsToRefund);
        }
    }
}
