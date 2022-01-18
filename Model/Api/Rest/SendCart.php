<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Model\Api\Rest;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address;
use Mulberry\Warranty\Api\Data\QueueInterface;
use Mulberry\Warranty\Api\Rest\SendCartServiceInterface;
use Mulberry\Warranty\Api\Rest\ServiceInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Directory\Model\CountryFactory;
use Mulberry\Warranty\Api\Config\HelperInterface;
use Mulberry\Warranty\Model\Product\Type;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Area;

class SendCart implements SendCartServiceInterface
{
    /**
     * @var array $itemsPayload
     */
    private $itemsPayload = [];

    /**
     * @var ServiceInterface $service
     */
    private $service;

    /**
     * @var HelperInterface $configHelper
     */
    private $configHelper;

    /**
     * @var OrderInterface $order
     */
    private $order;

    /**
     * @var CountryFactory $countryFactory
     */
    private $countryFactory;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * SendOrder constructor.
     *
     * @param ServiceInterface $service
     * @param HelperInterface $configHelper
     * @param CountryFactory $countryFactory
     * @param Emulation $emulation
     */
    public function __construct(
        ServiceInterface $service,
        HelperInterface $configHelper,
        CountryFactory $countryFactory,
        Emulation $emulation
    ) {
        $this->service = $service;
        $this->configHelper = $configHelper;
        $this->countryFactory = $countryFactory;
        $this->emulation = $emulation;
    }

    /**
     * Send order payload to Mulberry system
     *
     * @param OrderInterface $order
     *
     * @return mixed
     */
    public function sendCart(OrderInterface $order)
    {
        /**
         * Reset the values whenever we call the function to avoid caching.
         */
        $this->resetState();

        $this->order = $order;

        if (!$this->configHelper->isActive() || !$this->configHelper->isSendCartDataEnabled()) {
            return [];
        }

        $this->order = $order;
        $this->prepareItemsPayload();

        $payload = $this->getOrderPayload();
        $this->emulation->startEnvironmentEmulation($this->order->getStoreId(), Area::AREA_FRONTEND, true);
        $response = $this->service->makeRequest(self::CART_SEND_ENDPOINT_URL, $payload, ServiceInterface::POST);
        $this->emulation->stopEnvironmentEmulation();

        return $this->parseResponse($response);
    }

    /**
     * Reset the function state to avoid caching.
     */
    private function resetState()
    {
        $this->order = null;
        $this->itemsPayload = [];
    }

    /**
     * Prepare payload for order items
     */
    private function prepareItemsPayload()
    {
        foreach ($this->order->getAllVisibleItems() as $item) {
            /**
             * We don't need to send warranty products as a payload
             */
            if ($item->getProductType() === Type::TYPE_ID) {
                continue;
            }

            $this->prepareItemPayload($item);
        }
    }

    /**
     * @return array
     */
    private function getOrderPayload()
    {
        return [
            'line_items' => $this->itemsPayload,
            'billing_address' => $this->prepareAddressData(),
            'order_id' => $this->order->getIncrementId(),
        ];
    }

    /**
     * Retrieve billing address data for order payload
     *
     * @return array
     */
    private function prepareAddressData()
    {
        /**
         * @var $billingAddress Address
         */
        $billingAddress = $this->order->getBillingAddress();

        return [
            'first_name' => $billingAddress->getFirstname(),
            'last_name' => $billingAddress->getLastname(),
            'address1' => $billingAddress->getStreetLine(1),
            'phone' => $billingAddress->getTelephone(),
            'email' => $billingAddress->getEmail(),
            'city' => $billingAddress->getCity(),
            'zip' => $billingAddress->getPostcode(),
            'state' => $billingAddress->getRegionCode(),
            'country' => $this->countryFactory->create()->loadByCode($billingAddress->getCountryId())->getName(),
            'address2' => $billingAddress->getStreetLine(2),
            'country_code' => $billingAddress->getCountryId(),
            'province_code' => $billingAddress->getRegionCode(),
        ];
    }

    /**
     * Prepare payload single warranty item,
     * payload should contain separate object for each item purchased (no qty support in API at the moment)
     *
     * @param Item $item
     */
    private function prepareItemPayload(Item $item)
    {
        for ($i = 0; $i < (int)$item->getQtyOrdered(); $i++) {
            $this->itemsPayload[] = [
                'product_id' => $item->getSku(),
                'product_price' => $item->getPrice(),
                'product_title' => $item->getName(),
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
        return [
            'status' => $response['is_successful'] ? QueueInterface::STATUS_SYNCED : QueueInterface::STATUS_FAILED,
            'response' => $response
        ];
    }
}
