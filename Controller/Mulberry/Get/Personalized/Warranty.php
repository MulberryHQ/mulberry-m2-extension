<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Controller\Mulberry\Get\Personalized;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
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
     * @return mixed
     * @throws \Zend_Json_Exception
     */
    private function preparePayload()
    {
        //@codingStandardsIgnoreStart
        $json = file_get_contents('php://input');
        //@codingStandardsIgnoreEnd

        return \Zend_Json::decode($json);
    }
}
