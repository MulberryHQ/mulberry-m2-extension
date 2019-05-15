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
use Mulberry\Warranty\Api\Rest\SendCartServiceInterface;

class SendCart implements ObserverInterface
{
    /**
     * @var HelperInterface $configHelper
     */
    private $configHelper;

    /**
     * @var SendCartServiceInterface $orderSendService
     */
    private $orderSendService;

    /**
     * SendCart constructor.
     *
     * @param HelperInterface $configHelper
     * @param SendCartServiceInterface $orderSendService
     */
    public function __construct(HelperInterface $configHelper, SendCartServiceInterface $orderSendService)
    {
        $this->configHelper = $configHelper;
        $this->orderSendService = $orderSendService;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /**
         * @var Order $order
         */
        $order = $observer->getEvent()->getOrder();

        if ($this->configHelper->isActive()) {
            $this->orderSendService->sendCart($order);
        }
    }
}
