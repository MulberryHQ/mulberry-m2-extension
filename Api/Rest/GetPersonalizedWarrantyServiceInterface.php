<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Api\Rest;

interface GetPersonalizedWarrantyServiceInterface
{
    /**
     * Endpoint URI for warranty validation
     */
    const GET_WARRANTY_ENDPOINT_URL = '/api/get_personalized_warranty';

    /**
     * Proxy method to retrieve warranty products information from API
     *
     * @param array $payload
     *
     * @return string
     */
    public function getWarrantiesJson(array $payload = []);
}
