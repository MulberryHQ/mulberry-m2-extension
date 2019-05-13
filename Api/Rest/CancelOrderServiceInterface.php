<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Api\Rest;

use Magento\Sales\Api\Data\OrderInterface;

interface CancelOrderServiceInterface
{
    /**
     * Endpoint URI for sending order cancellation information
     */
    const ORDER_CANCEL_ENDPOINT_URL = '/api/order_cancelled';

    /**
     * Send order cancellation payload to Mulberry system
     *
     * @param OrderInterface $order
     *
     * @return mixed
     */
    public function cancelOrder(OrderInterface $order);
}
