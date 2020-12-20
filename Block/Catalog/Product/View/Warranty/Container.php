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
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\Data\Collection;
use Magento\Framework\Json\EncoderInterface as JsonEncoder;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Mulberry\Warranty\Api\Config\HelperInterface;

class Container extends View
{
    /**
     * @var HelperInterface $warrantyConfigHelper
     */
    private $warrantyConfigHelper;

    /**
     * @var Data $catalogHelper
     */
    private $catalogHelper;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    /**
     * Container constructor.
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
     * @param Data $catalogHelper
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
        Data $catalogHelper,
        UrlBuilder $imageUrlBuilder,
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

        $this->catalogHelper = $catalogHelper;
        $this->imageUrlBuilder = $imageUrlBuilder;
        $this->warrantyConfigHelper = $warrantyConfigHelper;
    }

    /**
     * Do not output block, if it's not activated in admin
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->warrantyConfigHelper->isActive() || !$this->getPartnerUrl() || !$this->getApiUrl()) {
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
     * @param $product
     * @return string
     */
    public function getProductDescription()
    {
        $product = $this->getProduct();
        $description = $product->getMetaDescription() ? $product->getMetaDescription() : $product->getDescription();
        $description = $this->stripTags($product->getDescription()); // Strip HTML tags
        $description = str_replace(["\r", "\n"], '', $description); // Remove new lines

        return $this->string->substr($description, 0, 255);
    }

    /**
     * Retrieve product gallery URLs
     *
     * @return Collection
     */
    public function getGalleryImagesInfo()
    {
        $result = [];
        $product = $this->getProduct();
        $images = $product->getMediaGalleryImages();
        if (!$images instanceof \Magento\Framework\Data\Collection) {
            return $images;
        }

        foreach ($images as $image) {
            $image->setData(
                'image_url',
                $this->imageUrlBuilder->getUrl($image->getFile(), 'product_page_image_large')
            );
        }

        foreach ($images as $image) {
            $result[] = ['src' => $image->getImageUrl()];
        }

        return json_encode($result);
    }

    /**
     * Return breadcrumbs information, if the "Use Categories Path for Product URLs" setting is enabled.
     */
    public function getBreadcrumbsInfo()
    {
        $breacrumbs = $this->catalogHelper->getBreadcrumbPath();
        $result = [];

        foreach ($breacrumbs as $key => $crumb) {
            if ($isCategory = $this->string->strpos($key, 'category') === 0) {
                $result[] = [
                    'category' => $crumb['label'],
                    'url' => $crumb['link'],
                ];
            }
        }

        return json_encode($result);
    }
}
