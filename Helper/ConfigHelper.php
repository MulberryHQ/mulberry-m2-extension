<?php
declare(strict_types=1);

/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Mulberry\Warranty\Api\Config\HelperInterface;

class ConfigHelper extends AbstractHelper implements HelperInterface
{
    /**
     * General Settings
     */
    const XML_PATH_IS_ACTIVE = 'mulberry_warranty/general/active';
    const XML_PATH_API_URL = 'mulberry_warranty/general/api_url';
    const XML_PATH_PARTNER_URL = 'mulberry_warranty/general/partner_url';
    const XML_PATH_PLATFORM_DOMAIN = 'mulberry_warranty/general/platform_domain';
    const XML_PATH_RETAILER_ID = 'mulberry_warranty/general/retailer_id';
    const XML_PATH_PRIVATE_TOKEN = 'mulberry_warranty/general/private_token';
    const XML_PATH_PUBLIC_TOKEN = 'mulberry_warranty/general/public_token';
    const XML_PATH_SEND_CART_DATA = 'mulberry_warranty/general/send_cart_data';
    const XML_PATH_ENABLE_FORCE_LOGGING = 'mulberry_warranty/general/enable_force_logging';
    const XML_PATH_ENABLE_CART_OFFERS = 'mulberry_warranty/general/enable_cart_offers';

    /**
     * {@inheritdoc}
     */
    public function isActive(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_IS_ACTIVE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getApiUrl(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_API_URL,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPartnerUrl(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PARTNER_URL,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPlatformDomain(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PLATFORM_DOMAIN,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRetailerId(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RETAILER_ID,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getApiToken(): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PRIVATE_TOKEN,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isSendCartDataEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_SEND_CART_DATA,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicToken()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PUBLIC_TOKEN,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isForceLoggingEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_FORCE_LOGGING,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function cartOffersEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_CART_OFFERS,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
