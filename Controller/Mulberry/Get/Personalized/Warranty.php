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
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey;
use Mulberry\Warranty\Model\Api\Rest\GetPersonalizedWarranty;

class Warranty extends Action
{
    /**
     * @var JsonFactory $resultJsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var GetPersonalizedWarranty $getPersonalizedWarrantyService
     */
    private $getPersonalizedWarrantyService;

    /**
     * @var FormKey|mixed
     */
    private $formKey;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        GetPersonalizedWarranty $getPersonalizedWarrantyService,
        FormKey $formKey
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->getPersonalizedWarrantyService = $getPersonalizedWarrantyService;
        $this->formKey = $formKey;

        /**
         * Add Magento 2.3 CsrfAwareAction compatibility
         */
        if (interface_exists("\Magento\Framework\App\CsrfAwareActionInterface")) {
            $request = $this->getRequest();
            if ($request instanceof RequestInterface && $request->isPost() && empty($request->getParam('form_key'))) {
                $request->setParam('form_key', $this->formKey->getFormKey());
            }
        }
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
}
