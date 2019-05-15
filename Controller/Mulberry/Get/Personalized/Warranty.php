<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Controller\Mulberry\Get\Personalized;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Mulberry\Warranty\Model\Api\Rest\GetPersonalizedWarranty;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Warranty extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var JsonFactory $resultJsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var GetPersonalizedWarranty $getPersonalizedWarrantyService
     */
    private $getPersonalizedWarrantyService;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        GetPersonalizedWarranty $getPersonalizedWarrantyService
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->getPersonalizedWarrantyService = $getPersonalizedWarrantyService;
    }

    /**
     * @return Json
     */
    public function execute()
    {
        /** @var Json $result */
        $result = $this->resultJsonFactory->create();
        try {
            $payload = $this->preparePayload();
        } catch (\Zend_Json_Exception $e) {
            $payload = [];
        }

        return $result->setData($this->getPersonalizedWarrantyService->getWarrantiesJson($payload));
    }

    /**
     * Get raw request data
     *
     * @return mixed
     * @throws \Zend_Json_Exception
     */
    private function preparePayload()
    {
        return \Zend_Json::decode($this->getRequest()->getContent());
    }

    /**
     * Create exception in case CSRF validation failed.
     * Return null if default exception will suffice.
     *
     * @param RequestInterface $request
     *
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Perform custom request validation.
     * Return null if default validation is needed.
     *
     * @param RequestInterface $request
     *
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
