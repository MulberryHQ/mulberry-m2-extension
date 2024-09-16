<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Block\Catalog\Product\View\Warranty;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View;
use Magento\Framework\Json\EncoderInterface as JsonEncoder;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Mulberry\Warranty\Api\Config\HelperInterface;
use Mulberry\Warranty\Api\ProductHelperInterface;

class Container extends View
{
    /**
     * @var HelperInterface $warrantyConfigHelper
     */
    private $warrantyConfigHelper;

    /**
     * @var ProductHelperInterface
     */
    private $mulberryProductHelper;

    /**
     * Container constructor.
     *
     * @param HelperInterface $warrantyConfigHelper
     * @param Context $context
     * @param EncoderInterface $urlEncoder
     * @param JsonEncoder $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param ProductHelperInterface $mulberryProductHelper
     * @param array $data
     */
    public function __construct(
        HelperInterface $warrantyConfigHelper,
        Context $context,
        EncoderInterface $urlEncoder,
        JsonEncoder $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        ProductHelperInterface $mulberryProductHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );

        $this->warrantyConfigHelper = $warrantyConfigHelper;
        $this->mulberryProductHelper = $mulberryProductHelper;
    }

    /**
     * Do not output block, if it's not activated in admin
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->warrantyConfigHelper->isActive() || !$this->getPartnerUrl() || !$this->getApiUrl() || !$this->getPublicToken() || !$this->getRetailerId()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return null|string
     */
    public function getPartnerUrl()
    {
        return $this->warrantyConfigHelper->getPartnerUrl();
    }

    /**
     * @return null|string
     */
    public function getApiUrl()
    {
        return $this->warrantyConfigHelper->getApiUrl();
    }

    /**
     * @return mixed
     */
    public function getPlatformDomain()
    {
        return $this->warrantyConfigHelper->getPlatformDomain() ?: $_SERVER['SERVER_NAME'];
    }

    /**
     * @return string|null
     */
    public function getRetailerId()
    {
        return $this->warrantyConfigHelper->getRetailerId();
    }

    /**
     * @return mixed
     */
    public function getPublicToken()
    {
        return $this->warrantyConfigHelper->getPublicToken();
    }

    /**
     * @return string
     */
    public function getProductDescription(): string
    {
        return $this->mulberryProductHelper->getProductDescription($this->getProduct());
    }

    /**
     * @return string
     */
    public function getGalleryImagesInfo(): string
    {
        return $this->_jsonEncoder->encode($this->mulberryProductHelper->getGalleryImagesInfo($this->getProduct()));
    }

    /**
     * @return string
     */
    public function getBreadcrumbsInfo(): string
    {
        return $this->_jsonEncoder->encode($this->mulberryProductHelper->getProductBreadcrumbs($this->getProduct()));
    }
}
