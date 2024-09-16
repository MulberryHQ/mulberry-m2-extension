<?php
declare(strict_types=1);

/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2024 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Stdlib\StringUtils;
use Mulberry\Warranty\Api\ProductHelperInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;

class ProductHelper implements ProductHelperInterface
{
    private UrlBuilder $imageUrlBuilder;
    private StringUtils $stringUtils;
    private FilterManager $filterManager;

    /**
     * ProductHelper constructor.
     *
     * @param UrlBuilder $imageUrlBuilder
     * @param StringUtils $stringUtils
     * @param FilterManager $filterManager
     */
    public function __construct(
        UrlBuilder $imageUrlBuilder,
        StringUtils $stringUtils,
        FilterManager $filterManager
    ) {
        $this->imageUrlBuilder = $imageUrlBuilder;
        $this->stringUtils = $stringUtils;
        $this->filterManager = $filterManager;
    }

    /**
     * @inheritDoc
     */
    public function getProductBreadcrumbs(ProductInterface $product): array
    {
        $result = [];
        $categories = $product->getCategoryCollection()->addAttributeToSelect('name');

        foreach ($categories as $category) {
            $result[] = [
                'category' => $category->getName(),
                'url' => $category->getUrl(),
            ];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getGalleryImagesInfo(ProductInterface $product): array
    {
        $result = [];
        $images = $product->getMediaGalleryImages();
        if (!$images instanceof Collection) {
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

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getProductDescription(ProductInterface $product): string
    {
        $description = $product->getMetaDescription() ? $product->getMetaDescription() : $product->getDescription();
        $description = $this->stripTags($product->getDescription()); // Strip HTML tags
        $description = str_replace(["\r", "\n"], '', $description); // Remove new lines

        return $this->stringUtils->substr($description, 0, 255);
    }

    /**
     * Copied over from \Magento\Framework\View\Element\AbstractBlock
     *
     * @param $data
     * @param $allowableTags
     * @param bool $allowHtmlEntities
     *
     * @return string
     */
    private function stripTags($data, $allowableTags = null, bool $allowHtmlEntities = false): string
    {
        $params = ['allowableTags' => $allowableTags, 'escape' => $allowHtmlEntities];

        return $data ? $this->filterManager->stripTags($data, $params) : '';
    }
}
