<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Controller\Adminhtml\System\Config;

use Mulberry\Warranty\Model\Crawler;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem\Driver\File;

class Generate extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var File
     */
    private $file;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Crawler $crawler
     * @param File $driverFile
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Crawler $crawler,
        File $driverFile,
        DirectoryList $directoryList
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->crawler = $crawler;
        $this->file = $driverFile;
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return Json
     */
    public function execute()
    {
        $page = $this->getRequest()->getParam('page');
        $pageData = $this->crawler->exportProductsByPage($page);
        $result = $this->resultJsonFactory->create();

        return $result->setData(
            ['success' => true, 'content' => $pageData['content'], 'lastPage' => $pageData['lastPage']]
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mulberry_Warranty::mulberry_warranty');
    }
}
