<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Mulberry\Warranty\Api\Config\HelperInterface;
use Mulberry\Warranty\Api\Rest\SendOrderServiceInterface;

class SendOrder implements ObserverInterface
{
    /**
     * @var HelperInterface $configHelper
     */
    private $configHelper;

    /**
     * @var SendOrderServiceInterface $orderSendService
     */
    private $orderSendService;

    /**
     * SendOrder constructor.
     *
     * @param HelperInterface $configHelper
     * @param SendOrderServiceInterface $orderSendService
     */
    public function __construct(HelperInterface $configHelper, SendOrderServiceInterface $orderSendService)
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
            $this->orderSendService->sendOrder($order);
        }
    }
}
