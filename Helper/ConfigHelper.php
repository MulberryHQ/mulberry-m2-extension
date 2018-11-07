<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
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
    const XML_PATH_API_TOKEN = 'mulberry_warranty/general/api_token';

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_IS_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getApiUrl()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_API_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPartnerUrl()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PARTNER_URL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPlatformDomain()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PLATFORM_DOMAIN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRetailerId()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RETAILER_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getApiToken()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_API_TOKEN,
            ScopeInterface::SCOPE_STORE
        );
    }
}
