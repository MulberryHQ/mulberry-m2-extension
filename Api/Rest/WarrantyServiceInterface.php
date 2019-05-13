<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Api\Rest;

interface WarrantyServiceInterface
{
    /**
     * Endpoint URI for warranty validation
     */
    const WARRANTY_VALIDATE_ENDPOINT_URL = '/api/validate_warranty/%s';

    /**
     * Retrieve warranty information from API using hash identifier
     *
     * @param string $hash
     *
     * @return mixed
     */
    public function getWarrantyByHash(string $hash);
}
