<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Api\Config;

interface HelperInterface
{
    /**
     * Return flag if Mulberry warranty functionality is enabled
     *
     * @return bool
     */
    public function isActive();

    /**
     * Retrieve API URL required for Mulberry JS requests
     *
     * @return string
     */
    public function getApiUrl();

    /**
     * Mulberry partner URL
     *
     * @return null|string
     */
    public function getPartnerUrl();

    /**
     * Mulberry platform domain name used within API
     *
     * @return null|string
     */
    public function getPlatformDomain();

    /**
     * Mulberry retailer ID for order payload
     *
     * @return null|string
     */
    public function getRetailerId();

    /**
     * API auth token that is used for calls
     *
     * @return null|string
     */
    public function getApiToken();
}
