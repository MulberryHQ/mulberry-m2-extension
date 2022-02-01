<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2022 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\Data\Collection;
use Mulberry\Warranty\Api\ProductHelperInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Catalog\Helper\Data;
use Magento\Framework\Json\EncoderInterface as JsonEncoder;

class ProductHelper extends AbstractHelper implements ProductHelperInterface
{
    /**
     * ProductHelper constructor.
     *
     * @param Context $context
     * @param Data $catalogHelper
     * @param UrlBuilder $imageUrlBuilder
     */
    public function __construct(Context $context,
        Data $catalogHelper,
        UrlBuilder $imageUrlBuilder
    ) {

        $this->catalogHelper = $catalogHelper;
        $this->imageUrlBuilder = $imageUrlBuilder;
    }

    /**
     * @param ProductInterface $product
     * @return mixed
     */
    public function getProductBreadcrumbs(ProductInterface $product)
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
     * @param ProductInterface $product
     * @return mixed
     */
    public function getGalleryImagesInfo(ProductInterface $product)
    {
        $result = [];
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

        return $result;
    }
}
