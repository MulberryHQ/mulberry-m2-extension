<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Model\Api\Rest;

use Magento\Framework\Serialize\Serializer\Json;
use Mulberry\Warranty\Api\Rest\ServiceInterface;
use Mulberry\Warranty\Api\Rest\WarrantyServiceInterface;

class Warranty implements WarrantyServiceInterface
{
    /**
     * @var ServiceInterface $service
     */
    private $service;

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
     * Warranty constructor.
     *
     * @param ServiceInterface $service
     */
    public function __construct(ServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @param string $hash
     *
     * @return array|mixed
     */
    public function getWarrantyByHash(string $hash)
    {
        $response = $this->service->makeRequest(sprintf(self::WARRANTY_VALIDATE_ENDPOINT_URL, $hash));

        return $this->parseResponse($response);
    }

    /**
     * Prepare data mapping for warranty product by hash
     *
     * @param $response
     *
     * @return array
     */
    private function parseResponse($response)
    {
        $result = [];

        /**
         * Warranty product information is stored in $response[0][0]
         */
        $warrantyProduct = (is_array($response) && isset($response['result'][0][0])) ? $response['result'][0][0] : [];

        if (!empty($warrantyProduct) && $this->validateWarrantyProductResponse($warrantyProduct)) {
            $result = [
                'warranty_price' => (float) $warrantyProduct['cost'],
                'service_type' => $warrantyProduct['service_type'],
                'warranty_hash' => $warrantyProduct['warranty_hash'],
                'duration_months' => $warrantyProduct['duration_months'],
                'name' => sprintf('Warranty - %s', $warrantyProduct['product']['name']),
            ];
        }

        return $result;
    }

    /**
     * Make sure we have all the necessary information
     *
     * @param $warrantyProduct
     *
     * @return bool
     */
    private function validateWarrantyProductResponse($warrantyProduct)
    {
        foreach ($this->warrantyAttributesMapping as $magentoAttribute => $apiNode) {
            $warrantyAttributeNode = $warrantyProduct;

            foreach ($apiNode as $node) {
                if (!isset($warrantyAttributeNode[$node])) {
                    return false;
                } else {
                    $warrantyAttributeNode = $warrantyAttributeNode[$node];
                }
            }
        }

        return true;
    }
}
