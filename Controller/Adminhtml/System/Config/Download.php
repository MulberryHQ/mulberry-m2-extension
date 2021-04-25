<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Controller\Adminhtml\System\Config;

use Exception;
use Mulberry\Warranty\Model\Crawler;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem\Driver\File;
use Zend\Http\Exception\RuntimeException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class Download extends Action
{

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var File
     */
    private $file;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * Download constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param File $driverFile
     * @param DirectoryList $directoryList
     * @param JsonSerializer $serializer
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        File $driverFile,
        DirectoryList $directoryList,
        JsonSerializer $serializer
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->file = $driverFile;
        $this->directoryList = $directoryList;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $fileContent = $this->readDataFromFile();
        $parsedJson = $this->parseContent($fileContent);

        return $result->setData(['success' => (bool) $parsedJson, 'content' => $parsedJson]);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mulberry_Warranty::mulberry_warranty');
    }

    /**
     * @return string
     */
    private function readDataFromFile() : string
    {
        try {
            $varFolder = $this->directoryList->getPath(DirectoryList::VAR_DIR);
            $filePath = sprintf('%s%s%s', $varFolder, DIRECTORY_SEPARATOR, Crawler::PAGINATED_CRAWLER_FILE_NAME);
            if ($this->file->isExists($filePath)) {
                return $this->file->fileGetContents($filePath);
            }
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Could not read file: %s', $e->getMessage()));
        }

        return '';
    }

    /**
     * @param string $fileContent
     * @return bool|float|int|mixed|\Services_JSON_Error|string|void
     */
    private function parseContent(string $fileContent)
    {
        $result = [];
        $jsonChunks = explode('|', $fileContent);

        foreach ($jsonChunks as $chunk) {
            if ($chunk) {
                $array = $this->serializer->unserialize($chunk);
                $result = array_merge($result, $array);
            }
        }

        return $this->serializer->serialize($result);
    }
}
