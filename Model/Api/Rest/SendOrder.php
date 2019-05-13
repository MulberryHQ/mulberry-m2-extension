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
use Mulberry\Warranty\Api\Rest\ServiceInterface;
use Mulberry\Warranty\Api\Rest\SendOrderServiceInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Directory\Model\CountryFactory;
use Mulberry\Warranty\Api\Config\HelperInterface;
use Mulberry\Warranty\Model\Product\Type;

class SendOrder implements SendOrderServiceInterface
{
    /**
     * @var ServiceInterface $service
     */
    private $service;

    /**
     * @var HelperInterface $configHelper
     */
    private $configHelper;

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
     * @var CountryFactory $countryFactory
     */
    private $countryFactory;

    /**
     * Data mapping for warranty attributes,
     * stored as follows:
     * Magento additional information key => ['Mulberry API key']
     *
     * @var array $warrantyAttributesMapping
     */
    protected $warrantyAttributesMapping = [
        'warranty_price' => ['cost'],
        'service_type' => ['service_type'],
        'warranty_hash' => ['warranty_hash'],
        'duration_months' => ['duration_months'],
        'product_name' => ['product', 'name'],
    ];

    /**
     * SendOrder constructor.
     *
     * @param ServiceInterface $service
     * @param HelperInterface $configHelper
     * @param CountryFactory $countryFactory
     */
    public function __construct(ServiceInterface $service, HelperInterface $configHelper, CountryFactory $countryFactory)
    {
        $this->service = $service;
        $this->configHelper = $configHelper;
        $this->countryFactory = $countryFactory;
    }

    /**
     * Send order payload to Mulberry system
     *
     * @param OrderInterface $order
     *
     * @return mixed
     */
    public function sendOrder(OrderInterface $order)
    {
        $this->order = $order;
        $this->prepareItemsPayload();

        if (!$this->orderHasWarrantyProducts) {
            return [];
        }

        $payload = $this->getOrderPayload();

        $response = $this->service->makeRequest(self::ORDER_SEND_ENDPOINT_URL, $payload, ServiceInterface::POST);

        return $this->parseResponse($response);
    }

    /**
     * Prepare payload for order items
     */
    private function prepareItemsPayload()
    {
        foreach ($this->order->getAllItems() as $item) {
            if ($item->getProductType() === Type::TYPE_ID) {
                $this->orderHasWarrantyProducts = true;
                $this->prepareItemPayload($item);
            }
        }
    }

    /**
     * @return array
     */
    private function getOrderPayload()
    {
        $order = $this->order;

        $payload = [
            'phone' => $order->getBillingAddress()->getTelephone(),
            'email' => $order->getCustomerEmail(),
            'retailer_id' => $this->configHelper->getRetailerId(),
            'cart_token' => $order->getIncrementId(),
            'billing_address' => $this->prepareAddressData(),
            'line_items' => $this->warrantyItemsPayload,
        ];

        return $payload;
    }

    /**
     * Retrieve billing address data for order payload
     *
     * @return array
     */
    private function prepareAddressData()
    {
        /**
         * @var $billingAddress \Magento\Sales\Model\Order\Address
         */
        $billingAddress = $this->order->getBillingAddress();

        return [
            'first_name' => $billingAddress->getFirstname(),
            'last_name' => $billingAddress->getLastname(),
            'address1' => $billingAddress->getStreetLine(1),
            'address2' => $billingAddress->getStreetLine(2),
            'city' => $billingAddress->getCity(),
            'state' => $billingAddress->getRegionCode(),
            'zip' => $billingAddress->getPostcode(),
            'country' => $this->countryFactory->create()->loadByCode($billingAddress->getCountryId())->getName(),
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
        $warrantyProductData = $item->getBuyRequest()->getWarrantyProduct();

        for ($i = 0; $i < (int) $item->getQtyOrdered(); $i++) {
            $this->warrantyItemsPayload[] = [
                'id' => $item->getId(),
                'quantity' => (int) 1,
                'warranty_hash' => $warrantyProductData['warranty_hash']
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
