<?php
declare(strict_types=1);
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2024 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Mulberry\Warranty\Api\Config\HelperInterface;

class ConfigViewModel implements ArgumentInterface
{
    private HelperInterface $warrantyConfigHelper;

    /**
     * @param HelperInterface $warrantyConfigHelper
     */
    public function __construct(HelperInterface $warrantyConfigHelper)
    {
        $this->warrantyConfigHelper = $warrantyConfigHelper;
    }

    /**
     * @return null|string
     */
    public function getPartnerUrl(): ?string
    {
        return $this->warrantyConfigHelper->getPartnerUrl();
    }

    /**
     * @return null|string
     */
    public function getApiUrl(): ?string
    {
        return $this->warrantyConfigHelper->getApiUrl();
    }

    /**
     * @return string|null
     */
    public function getPlatformDomain(): ?string
    {
        return $this->warrantyConfigHelper->getPlatformDomain() ?? $_SERVER['SERVER_NAME'];
    }

    /**
     * @return string|null
     */
    public function getRetailerId(): ?string
    {
        return $this->warrantyConfigHelper->getRetailerId();
    }

    /**
     * @return string|null
     */
    public function getPublicToken(): ?string
    {
        return $this->warrantyConfigHelper->getPublicToken();
    }

    /**
     * @return bool
     */
    public function canRender(): bool
    {
        return $this->warrantyConfigHelper->isActive() && $this->getPartnerUrl() && $this->getApiUrl() && $this->getPublicToken() && $this->getRetailerId();
    }
}
