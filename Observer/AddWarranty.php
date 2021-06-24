<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart\RequestInfoFilterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Mulberry\Warranty\Api\Config\HelperInterface;
use Mulberry\Warranty\Api\ItemOptionInterface;
use Mulberry\Warranty\Api\ItemUpdaterInterface as ItemUpdater;
use Psr\Log\LoggerInterface;

class AddWarranty implements ObserverInterface
{
    /**
     * @var RequestInterface $request
     */
    private $request;

    /**
     * @var ItemUpdater $warrantyItemUpdater
     */
    private $warrantyItemUpdater;

    /**
     * @var StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface $productRepository
     */
    private $productRepository;

    /**
     * @var RequestInfoFilterInterface $requestInfoFilter
     */
    private $requestInfoFilter;

    /**
     * @var ManagerInterface $messageManager
     */
    private $messageManager;

    /**
     * @var HelperInterface $helper
     */
    private $helper;

    /**
     * @var ItemOptionInterface $helper
     */
    private $itemOptionHelper;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * AddWarranty constructor.
     *
     * @param RequestInterface $request
     * @param ItemUpdater $itemUpdater
     * @param StoreManagerInterface $storeManager
     * @param RequestInfoFilterInterface $requestInfoFilter
     * @param ProductRepositoryInterface $productRepository
     * @param ManagerInterface $messageManager
     * @param HelperInterface $helper
     * @param ItemOptionInterface $itemOptionHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        ItemUpdater $itemUpdater,
        StoreManagerInterface $storeManager,
        RequestInfoFilterInterface $requestInfoFilter,
        ProductRepositoryInterface $productRepository,
        ManagerInterface $messageManager,
        HelperInterface $helper,
        ItemOptionInterface $itemOptionHelper,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->warrantyItemUpdater = $itemUpdater;
        $this->storeManager = $storeManager;
        $this->requestInfoFilter = $requestInfoFilter;
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->itemOptionHelper = $itemOptionHelper;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $originalProduct = $observer->getEvent()->getProduct();
        $warrantyProduct = null;
        try {
            /**
             * Add warranty products equal to the amount of original product added to cart.
             */
            if ($this->helper->isActive()) {
                /**
                 * @var Product $originalProduct
                 * @var Quote $quote
                 * @var Quote\Item $originalQuoteItem
                 */
                $originalQuoteItem = $observer->getEvent()->getQuoteItem();
                $params = $this->request->getParams();
                $quote = $originalQuoteItem->getQuote();
                $warrantyProductsToAdd = isset($params['qty']) ? $params['qty'] : 1;

                if (array_key_exists('warranty', $params)) {
                    $warrantySku = $params['warranty']['sku'];
                    $isValidWarrantyHash = false;

                    /**
                     * Check whether we need to add warranty for this product or not
                     */
                    if (isset($params['warranty']['hash']) && !empty($params['warranty']['hash']) && $warrantySku === $this->getSelectedProductSku($originalProduct)) {
                        $isValidWarrantyHash = true;
                    }

                    /**
                     * Process additional warranty product add-to-cart
                     */
                    if ($originalProduct && $originalProduct->getId() && $isValidWarrantyHash) {
                        $warrantyHash = $params['warranty']['hash'];

                        /**
                         * Prepare buyRequest and other options for warranty quote item
                         */
                        $options = $this->itemOptionHelper->prepareWarrantyOption($originalQuoteItem, $warrantyHash);
                        $warrantyOptions = $this->itemOptionHelper->prepareWarrantyInformation($warrantyHash);

                        /**
                         * @var Product $warrantyProduct
                         */
                        $warrantyProduct = $this->getWarrantyPlaceholderProduct($warrantyOptions);

                        $this->warrantyItemUpdater->addWarrantyItemOption($warrantyProduct, $options);
                        $this->warrantyItemUpdater->addAdditionalOptions($warrantyProduct, $warrantyOptions);

                        /**
                         * Quote add to cart logic should be there to avoid duplicate in add-to-cart functionality
                         */
                        $options['qty'] = $warrantyProductsToAdd;
                        $warrantyQuoteItem = $quote->addProduct(
                            $warrantyProduct,
                            $this->getProductRequest($options)
                        );

                        /**
                         * Custom price should be set after quote item has been prepared
                         */
                        $this->warrantyItemUpdater->setCustomWarrantyItemPrice($warrantyQuoteItem, $options);
                    }
                }
            }
        } catch (NoSuchEntityException $e) {
            if ($warrantyProduct != null && $warrantyProduct->getId()) {
                $this->messageManager->addErrorMessage(
                    __('We were not able to add the %1 to cart, but we did add the %2 to cart',
                        $warrantyProduct->getName(),
                        $originalProduct->getName()
                    )
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __('We were not able to add the warranty product to cart, but we did add the %1 to cart',
                        $originalProduct->getName()
                    )
                );
            }
            $this->logger->critical(__('Unable to add warranty product to cart. Exception message: %1'), $e->getMessage());
        } catch (LocalizedException $e) {
            if ($warrantyProduct != null && $warrantyProduct->getId()) {
                $this->messageManager->addErrorMessage(
                    __('We were not able to add the %1 to cart, but we did add the %2 to cart',
                        $warrantyProduct->getName(),
                        $originalProduct->getName()
                    )
                );
            } else {
                $this->messageManager->addErrorMessage(
                    __('We were not able to add the warranty product to cart, but we did add the %1 to cart',
                        $originalProduct->getName()
                    )
                );
            }
            $this->logger->critical(__('Unable to add warranty product to cart. Exception message: %1'), $e->getMessage());
        }
    }

    /**
     * @param Product $product
     *
     * @return string
     */
    private function getSelectedProductSku(Product $product)
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

    /**
     * Retrieve Magento placeholder product to be used as a warranty product
     *
     * @param array $warrantyOptions
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws NoSuchEntityException
     */
    protected function getWarrantyPlaceholderProduct(array $warrantyOptions = [])
    {
        $placeholderSku = (is_array($warrantyOptions) && isset($warrantyOptions['duration_months'])) ? sprintf('mulberry-warranty-%s-months', $warrantyOptions['duration_months']) : 'mulberry-warranty-product';

        return $this->productRepository->get(
            $placeholderSku,
            false,
            $this->storeManager->getStore()->getId(),
            true
        );
    }
}
