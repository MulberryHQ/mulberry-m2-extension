<?php
declare(strict_types=1);
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2024 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Model\Processor;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Checkout\Helper\Cart;
use Magento\Checkout\Model\Cart\RequestInfoFilterInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mulberry\Warranty\Api\AddWarrantyProcessorInterface;
use Mulberry\Warranty\Api\ItemOptionInterface;
use Mulberry\Warranty\Api\ItemUpdaterInterface as ItemUpdater;

class AddWarranty implements AddWarrantyProcessorInterface
{
    private ItemUpdater $warrantyItemUpdater;
    private StoreManagerInterface $storeManager;
    private ProductRepositoryInterface $productRepository;
    private RequestInfoFilterInterface $requestInfoFilter;
    private ItemOptionInterface $itemOptionHelper;
    private Cart $cartHelper;

    /**
     * Whitelisted product types eligible for the warranty
     *
     * @var array $allowedProductTypes
     */
    protected array $allowedProductTypes = [
        Type::DEFAULT_TYPE,
        Configurable::TYPE_CODE,
    ];

    /**
     * AddWarranty constructor.
     *
     * @param ItemUpdater $itemUpdater
     * @param StoreManagerInterface $storeManager
     * @param RequestInfoFilterInterface $requestInfoFilter
     * @param ProductRepositoryInterface $productRepository
     * @param ItemOptionInterface $itemOptionHelper
     * @param Cart $cartHelper
     */
    public function __construct(
        ItemUpdater $itemUpdater,
        StoreManagerInterface $storeManager,
        RequestInfoFilterInterface $requestInfoFilter,
        ProductRepositoryInterface $productRepository,
        ItemOptionInterface $itemOptionHelper,
        Cart $cartHelper
    ) {
        $this->warrantyItemUpdater = $itemUpdater;
        $this->storeManager = $storeManager;
        $this->requestInfoFilter = $requestInfoFilter;
        $this->productRepository = $productRepository;
        $this->itemOptionHelper = $itemOptionHelper;
        $this->cartHelper = $cartHelper;
    }

    /**
     * @inheritDoc
     */
    public function execute(CartItemInterface $quoteItem, array $requestParams = []): ?CartItemInterface
    {
        if (!$this->validate($quoteItem, $requestParams)) {
            return null;
        }

        /**
         * Prepare buyRequest and other options for warranty quote item
         */
        $warrantyHash = $requestParams['warranty']['hash'];
        $options = $this->itemOptionHelper->prepareWarrantyOption($quoteItem, $warrantyHash);
        $warrantyOptions = $this->itemOptionHelper->prepareWarrantyInformation($warrantyHash);

        /**
         * @var Product $warrantyProduct
         */
        $warrantyProduct = $this->getWarrantyPlaceholderProduct($warrantyOptions);

        $this->warrantyItemUpdater->addWarrantyItemOption($warrantyProduct, $options);
        $this->warrantyItemUpdater->addAdditionalOptions($warrantyProduct, $warrantyOptions);

        $options['qty'] = $requestParams['qty'] ?? 1;

        $cart = $this->cartHelper->getCart();
        $warrantyQuoteItem = $cart->getQuote()->addProduct(
            $warrantyProduct,
            $this->getProductRequest($options)
        );

        $this->warrantyItemUpdater->setCustomWarrantyItemPrice($warrantyQuoteItem, $options);

        /**
         * This is needed to calculate row totals against the item.
         */
        $cart->getQuote()->removeAllAddresses();

        /**
         * Save cart and re-calculate quote totals
         */
        $cart->getQuote()->setTotalsCollectedFlag(false);
        $this->cartHelper->getCart()->save();

        return $warrantyQuoteItem;
    }

    /**
     * Retrieve Magento placeholder product to be used as a warranty product
     *
     * @param array $warrantyOptions
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getWarrantyPlaceholderProduct(array $warrantyOptions = []): ProductInterface
    {
        $placeholderSku = (is_array($warrantyOptions) && isset($warrantyOptions['duration_months']))
            ? sprintf('mulberry-warranty-%s-months', $warrantyOptions['duration_months'])
            : 'mulberry-warranty-product';

        return $this->productRepository->get(
            $placeholderSku,
            false,
            $this->storeManager->getStore()->getId(),
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function validate(CartItemInterface $quoteItem, array $requestParams = []): bool
    {
        if (!$this->isProductTypeAllowed($quoteItem)) {
            return false;
        }

        if (!array_key_exists('warranty', $requestParams)) {
            return false;
        }

        /**
         * Make sure the required fields are presented.
         */
        if (empty($requestParams['warranty']['sku']) || empty($requestParams['warranty']['hash'])) {
            return false;
        }

        /**
         * Check whether we need to add warranty for this product or not
         */
        if ($requestParams['warranty']['sku'] !== $this->getSelectedProductSku($quoteItem->getProduct())) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function isProductTypeAllowed(CartItemInterface $quoteItem): bool
    {
        return in_array($quoteItem->getProductType(), $this->allowedProductTypes, true);
    }

    /**
     * @param ProductInterface $product
     *
     * @return string
     */
    private function getSelectedProductSku(ProductInterface $product): string
    {
        return $product->getSku();
    }

    /**
     * @param array $requestInfo
     *
     * @return DataObject
     * @throws LocalizedException
     */
    private function getProductRequest(array $requestInfo = []): DataObject
    {
        if ($requestInfo instanceof DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new DataObject(['qty' => $requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = new DataObject($requestInfo);
        } else {
            throw new LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

        $this->requestInfoFilter->filter($request);

        return $request;
    }
}
