<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Model\Api\Rest;

use Magento\Framework\App\Area;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Item;
use Mulberry\Warranty\Api\Rest\CancelOrderServiceInterface;
use Mulberry\Warranty\Api\Rest\ServiceInterface;
use Mulberry\Warranty\Model\Product\Type;
use Magento\Store\Model\App\Emulation;

class CancelOrder implements CancelOrderServiceInterface
{
    /**
     * @var ServiceInterface $service
     */
    private $service;

    /**
     * @var bool $orderHasWarrantyProducts
     */
    private $orderHasWarrantyProducts = false;

    /**
     * @var array $warrantyItemsPayload
     */
    private $warrantyItemsPayload = [];

    /**
     * @var OrderInterface $order
     */
    private $order;

    /**
     * @var TimezoneInterface $date
     */
    private $date;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * SendOrder constructor.
     *
     * @param ServiceInterface $service
     * @param TimezoneInterface $date
     * @param Emulation $emulation
     */
    public function __construct(
        ServiceInterface $service,
        TimezoneInterface $date,
        Emulation $emulation
    ) {
        $this->service = $service;
        $this->date = $date;
        $this->emulation = $emulation;
    }

    /**
     * Send order payload to Mulberry system
     *
     * @param OrderInterface $order
     *
     * @return mixed
     */
    public function cancelOrder(OrderInterface $order)
    {
        $this->order = $order;
        $this->prepareItemsPayload();
        if (!$this->orderHasWarrantyProducts) {
            return [];
        }

        $payload = $this->getOrderCancellationPayload();

        $this->emulation->startEnvironmentEmulation($this->order->getStoreId(), Area::AREA_FRONTEND, true);
        $response = $this->service->makeRequest(self::ORDER_CANCEL_ENDPOINT_URL, $payload, ServiceInterface::POST);
        $this->emulation->stopEnvironmentEmulation();

        return $this->parseResponse($response);
    }

    /**
     * Prepare payload for order items
     */
    private function prepareItemsPayload()
    {
        /**
         * @var Item $item
         */
        foreach ($this->order->getAllItems() as $item) {
            if ($item->getProductType() === Type::TYPE_ID && $item->getQtyCanceled()) {
                $this->orderHasWarrantyProducts = true;
                $this->prepareItemPayload($item);
            }
        }
    }

    /**
     * Prepare full payload to be sent, when Magento order is cancelled
     *
     * @return array
     */
    private function getOrderCancellationPayload()
    {
        $payload = [
            'cancelled_date' => $this->date->date()->format('Y-m-d'),
            'order_id' => $this->order->getIncrementId(),
            'line_items' => $this->warrantyItemsPayload,
        ];

        return $payload;
    }

    /**
     * Prepare cancellation payload for order item
     *
     * @param Item $item
     */
    private function prepareItemPayload(Item $item)
    {
        $originalProductData = $item->getBuyRequest()->getOriginalProduct();

        for ($i = 0; $i < (int) $item->getQtyCanceled(); $i++) {
            $this->warrantyItemsPayload[] = [
                'product_id' => $originalProductData['product_sku'],
            ];
        }
    }

    /**
     * @param $response
     *
     * @return array
     */
    private function parseResponse($response)
    {
        return [];
    }
}
