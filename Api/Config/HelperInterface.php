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
    public function isActive(): bool;

    /**
     * Retrieve API URL required for Mulberry JS requests
     *
     * @return string
     */
    public function getApiUrl(): string;

    /**
     * Mulberry partner URL
     *
     * @return null|string
     */
    public function getPartnerUrl(): ?string;

    /**
     * Mulberry platform domain name used within API
     *
     * @return null|string
     */
    public function getPlatformDomain(): ?string;

    /**
     * Mulberry retailer ID for order payload
     *
     * @return null|string
     */
    public function getRetailerId(): ?string;

    /**
     * API auth token that is used for calls
     *
     * @return null|string
     */
    public function getApiToken(): ?string;

    /**
     * Returns public token used to initialize offer on the FE
     *
     * @return mixed
     */
    public function getPublicToken();

    /**
     * Yes/No flag whether it's required to send order data on order place
     *
     * @return mixed
     */
    public function isSendCartDataEnabled(): bool;

    /**
     * Yes/No flag to force log Mulberry API request/response information
     *
     * @return mixed
     */
    public function isForceLoggingEnabled();

    /**
     * Yes/No flag for the cart placements display
     *
     * @return bool
     */
    public function cartOffersEnabled(): bool;
}
