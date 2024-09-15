<?php
declare(strict_types=1);
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2024 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Observer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Mulberry\Warranty\Api\AddWarrantyProcessorInterface;
use Mulberry\Warranty\Api\Config\HelperInterface;
use Psr\Log\LoggerInterface;

class AddWarranty implements ObserverInterface
{
    private ManagerInterface $messageManager;
    private HelperInterface $helper;
    private LoggerInterface $logger;
    private UrlInterface $url;
    private ?AddWarrantyProcessorInterface $addWarrantyProcessor;
    private Cart $cartHelper;

    /**
     * AddWarranty constructor.
     *
     * @param ManagerInterface $messageManager
     * @param HelperInterface $helper
     * @param LoggerInterface $logger
     * @param UrlInterface $url
     * @param AddWarrantyProcessorInterface $addWarrantyProcessor
     * @param Cart $cartHelper
     */
    public function __construct(
        ManagerInterface $messageManager,
        HelperInterface $helper,
        LoggerInterface $logger,
        UrlInterface $url,
        AddWarrantyProcessorInterface $addWarrantyProcessor,
        Cart $cartHelper
    ) {
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->url = $url;
        $this->addWarrantyProcessor = $addWarrantyProcessor;
        $this->cartHelper = $cartHelper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isActive()) {
            return;
        }

        $originalProduct = $observer->getProduct();

        try {
            /**
             * @var Product $originalProduct
             */
            $params = $observer->getRequest()->getParams();
            $cart = $this->cartHelper->getCart();
            $quoteItem = $cart->getQuote()->getItemByProduct($originalProduct);

            if (!$quoteItem) {
                return;
            }

            $addedWarrantyItem = $this->addWarrantyProcessor->execute($quoteItem, $params);

            if ($addedWarrantyItem instanceof CartItemInterface) {
                $this->messageManager->addComplexSuccessMessage(
                    'addCartSuccessMessage',
                    [
                        'product_name' => $addedWarrantyItem->getName(),
                        'cart_url' => $this->getCartUrl(),
                    ]
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We were not able to add the warranty product to cart, but we did add the %1 to cart',
                    $originalProduct->getName()
                )
            );

            $this->logger->critical(__('Unable to add warranty product to cart. Exception message: %1', $e->getMessage()));
        }
    }

    /**
     * Returns cart url
     *
     * @return string
     */
    private function getCartUrl(): string
    {
        return $this->url->getUrl('checkout/cart', ['_secure' => true]);
    }

    /**
     * @deprecated
     * Use getWarrantyPlaceholderProduct in \Mulberry\Warranty\Model\Processor\AddWarranty instead
     */
    public function getWarrantyPlaceholderProduct(array $warrantyOptions = []): ProductInterface
    {
        return $this->addWarrantyProcessor->getWarrantyPlaceholderProduct($warrantyOptions);
    }
}
