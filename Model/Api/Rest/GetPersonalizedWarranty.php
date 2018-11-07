<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Model\Api\Rest;

use Mulberry\Warranty\Api\Rest\ServiceInterface;
use Mulberry\Warranty\Api\Rest\GetPersonalizedWarrantyServiceInterface;
use Mulberry\Warranty\Api\Config\HelperInterface;

class GetPersonalizedWarranty implements GetPersonalizedWarrantyServiceInterface
{
    /**
     * @var ServiceInterface $service
     */
    private $service;

    /**
     * GetPersonalizedWarranty constructor.
     *
     * @param ServiceInterface $service
     */
    public function __construct(ServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function getWarrantiesJson(array $payload = [])
    {
        $payload = $this->getPayload($payload);

        $response = $this->service->makeRequest(self::GET_WARRANTY_ENDPOINT_URL, $payload, ServiceInterface::POST);

        return $this->parseResponse($response);
    }

    /**
     * @param $response
     *
     * @return array|mixed
     */
    private function parseResponse($response)
    {
        return is_array($response) && isset($response['result']) ? $response['result'] : [];
    }

    /**
     * @param $payload
     *
     * @return mixed
     */
    private function getPayload(array $payload = [])
    {
        return $payload;
    }
}
