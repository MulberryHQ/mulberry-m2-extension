<?php
declare(strict_types=1);
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2024 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Controller\Cart;

use Magento\Checkout\Helper\Cart;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Mulberry\Warranty\Api\AddWarrantyProcessorInterface;
use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote;

class Add implements HttpPostActionInterface
{
    /**
     * @var AddWarrantyProcessorInterface
     */
    private AddWarrantyProcessorInterface $addWarrantyProcessor;

    /**
     * @var FormKeyValidator
     */
    private FormKeyValidator $formKeyValidator;

    /**
     * @var ResultJsonFactory
     */
    private ResultJsonFactory $resultJsonFactory;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var MessageManagerInterface
     */
    private MessageManagerInterface $messageManager;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var CheckoutSession
     */
    private CheckoutSession $checkoutSession;

    /**
     * @var Cart $cartHelper
     */
    private Cart $cartHelper;

    public function __construct(
        FormKeyValidator $formKeyValidator,
        AddWarrantyProcessorInterface $addWarrantyProcessor,
        ResultJsonFactory $resultJsonFactory,
        RequestInterface $request,
        MessageManagerInterface $messageManager,
        LoggerInterface $logger,
        CheckoutSession $checkoutSession,
        Cart $cartHelper
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->addWarrantyProcessor = $addWarrantyProcessor;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->cartHelper = $cartHelper;
    }

    /**
     * Add to cart warranty
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if (!$this->formKeyValidator->validate($this->request)) {
            $result = [
                'status' => false,
                'error' => __('We were not able to add the warranty product to cart. Please refresh the page and try again.'),
            ];

            return $this->jsonResponse($result);
        }

        try {
            $addedWarrantyItem = $this->addWarrantyProcessor->execute(
                $this->getQuoteItem(),
                $this->getSanitizedWarrantyParams()
            );

            if ($this->request->getParam('force_page_reload', false) === 'true') {
                if ($addedWarrantyItem instanceof CartItemInterface) {
                    $this->messageManager->addComplexSuccessMessage(
                        'addCartSuccessMessage',
                        [
                            'product_name' => $addedWarrantyItem->getName(),
                            'cart_url' => $this->cartHelper->getCartUrl(),
                        ]
                    );
                }
            }

            $result = [
                'status' => true,
                'force_page_reload' => $this->request->getParam('force_page_reload', false) === 'true',
                'message' => __('Product warranty was added successfully.'),
            ];
        } catch (\Exception $e) {
            $this->logger->error('Error adding product warranty: ' . $e->getMessage());
            $result = [
                'status' => false,
                'error' => __('There was an error adding the product warranty.'),
            ];
        }

        return $this->jsonResponse($result);
    }

    /**
     * JSON response builder
     *
     * @param array $data
     * @return ResultInterface
     */
    private function jsonResponse(array $data = []): ResultInterface
    {
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($data);
        return $resultJson;
    }

    /**
     * Retrieve quote item
     *
     * @return CartItemInterface
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getQuoteItem(): CartItemInterface
    {
        $itemId = $this->request->getParam('item_id');

        $item = $this->checkoutSession->getQuote()->getItemById($itemId);

        if (!$item instanceof CartItemInterface) {
            throw new LocalizedException(__("The quote item isn't found. Verify the item and try again."));
        }

        return $item;
    }

    /**
     * @return array
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getSanitizedWarrantyParams(): array
    {
        $warrantyParams = $this->request->getParam('warranty', []);
        $quoteItem = $this->getQuoteItem();

        return [
            'warranty' => [
                'hash' => $warrantyParams['warranty_hash'] ?? '',
                'sku' => $quoteItem->getSku(),
            ],
            'qty' => $quoteItem->getQty(),
        ];
    }
}
