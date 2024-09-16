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

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Mulberry\Warranty\Api\AddWarrantyProcessorInterface;
use Mulberry\Warranty\Api\Config\HelperInterface;
use Mulberry\Warranty\Api\ItemOptionInterface;
use Mulberry\Warranty\Api\ProductHelperInterface;
use Mulberry\Warranty\Model\Product\Type;

class CartItemViewModel implements ArgumentInterface
{
    private AddWarrantyProcessorInterface $addWarrantyProcessor;
    private ItemOptionInterface $itemOption;
    private Json $json;
    private ProductHelperInterface $mulberryProductHelper;
    private HelperInterface $configHelper;

    /**
     * @param AddWarrantyProcessorInterface $addWarrantyProcessor
     * @param ItemOptionInterface $itemOption
     * @param Json $json
     * @param ProductHelperInterface $mulberryProductHelper
     * @param HelperInterface $configHelper
     */
    public function __construct(
        AddWarrantyProcessorInterface $addWarrantyProcessor,
        ItemOptionInterface $itemOption,
        Json $json,
        ProductHelperInterface $mulberryProductHelper,
        HelperInterface $configHelper
    ) {
        $this->addWarrantyProcessor = $addWarrantyProcessor;
        $this->itemOption = $itemOption;
        $this->json = $json;
        $this->mulberryProductHelper = $mulberryProductHelper;
        $this->configHelper = $configHelper;
    }

    /**
     * Retrieve product description for the quote item
     *
     * @param CartItemInterface $quoteItem
     *
     * @return string
     */
    public function getProductDescription(CartItemInterface $quoteItem): string
    {
        return $this->mulberryProductHelper->getProductDescription($quoteItem->getProduct());
    }

    /**
     * @param CartItemInterface $quoteItem
     *
     * @return string
     */
    public function getGalleryImagesInfo(CartItemInterface $quoteItem): string
    {
        return $this->json->serialize($this->mulberryProductHelper->getGalleryImagesInfo($quoteItem->getProduct()));
    }

    /**
     * @param CartItemInterface $quoteItem
     *
     * @return string
     */
    public function getBreadcrumbsInfo(CartItemInterface $quoteItem): string
    {
        return $this->json->serialize($this->mulberryProductHelper->getProductBreadcrumbs($quoteItem->getProduct()));
    }

    /**
     * @param CartItemInterface $quoteItem
     *
     * @return bool
     */
    public function isEligible(CartItemInterface $quoteItem): bool
    {
        if (!$this->addWarrantyProcessor->isProductTypeAllowed($quoteItem)) {
            return false;
        }

        if ($this->hasMatchingWarranty($quoteItem)) {
            return false;
        }

        if (!$this->isShoppingCartOffersEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * Check if shopping cart offers enabled
     *
     * @return bool
     */
    public function isShoppingCartOffersEnabled(): bool
    {
        return $this->configHelper->cartOffersEnabled();
    }

    /**
     * Check if there's a warranty item matching the given quote item's ID
     *
     * @param CartItemInterface $quoteItem
     *
     * @return bool
     */
    private function hasMatchingWarranty(CartItemInterface $quoteItem): bool
    {
        $quote = $quoteItem->getQuote();

        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductType() === Type::TYPE_ID) {
                $warrantyOptions = $this->itemOption->getWarrantyOption($item);
                $originalProduct = $warrantyOptions->getData('original_product');

                if (is_array($originalProduct)
                    && isset($originalProduct['item_id'])
                    && $originalProduct['item_id'] === $quoteItem->getItemId()
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
