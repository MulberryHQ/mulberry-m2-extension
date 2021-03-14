<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mulberry\Warranty\Api\Config\HelperInterface;
use Mulberry\Warranty\Api\QueueProcessorInterface;
use Mulberry\Warranty\Api\Rest\SendCartServiceInterface;

class AddOrderToQueue implements ObserverInterface
{
    /**
     * @var HelperInterface $configHelper
     */
    private $configHelper;

    /**
     * @var QueueProcessorInterface $queueProcessor
     */
    private $queueProcessor;

    /**
     * SendCart constructor.
     *
     * @param HelperInterface $configHelper
     * @param QueueProcessorInterface $queueProcessor
     */
    public function __construct(
        HelperInterface $configHelper,
        QueueProcessorInterface $queueProcessor
    ) {
        $this->configHelper = $configHelper;
        $this->queueProcessor = $queueProcessor;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        /**
         * Check if Mulberry integration is enabled.
         */
        if ($this->configHelper->isActive()) {
            /**
             * Add order to "send cart" queue if it's enabled
             */
            if ($this->configHelper->isSendCartDataEnabled()) {
                $this->queueProcessor->addToQueue($order, QueueProcessorInterface::ACTION_TYPE_CART);
            }

            /**
             * Add order to queue if there's an extended warranty purchased
             */
            $this->queueProcessor->addToQueue($order, QueueProcessorInterface::ACTION_TYPE_ORDER);
        }
    }
}
