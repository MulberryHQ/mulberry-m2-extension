<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Mulberry\Warranty\Api\Data\QueueInterface;

interface QueueProcessorInterface
{
    /**
     * Supported action types.
     */
    const ACTION_TYPE_ORDER = 'order';
    const ACTION_TYPE_CART = 'cart';

    /**
     * Add record to the Mulberry queue
     *
     * @param OrderInterface $order
     * @param string $type
     */
    public function addToQueue(OrderInterface $order, string $type) : void;

    /**
     * Process Mulberry queue record
     *
     * @param QueueInterface $queue
     * @return mixed
     */
    public function process(OrderInterface $order, $actionType) : bool;

    /**
     * @return OrderCollection
     */
    public function getOrdersToExport() : OrderCollection;

    /**
     * @return OrderCollection
     */
    public function getCartsToExport() : OrderCollection;
}
