<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Api\Rest;

use Magento\Sales\Api\Data\OrderInterface;

interface SendOrderServiceInterface
{
    /**
     * Endpoint URI for sending order information
     */
    const ORDER_SEND_ENDPOINT_URL = '/api/checkout';

    /**
     * Send order payload to Mulberry system
     *
     * @param OrderInterface $order
     *
     * @return mixed
     */
    public function sendOrder(OrderInterface $order);
}
