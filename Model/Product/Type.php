<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Psr\Log\LoggerInterface;

class Type extends AbstractType
{
    const TYPE_ID = 'warranty';

    /**
     * @var MessageManager $messageManager
     */
    private $messageManager;

    public function __construct(
        Option $catalogProductOption,
        Config $eavConfig,
        Product\Type $catalogProductType,
        ManagerInterface $eventManager,
        Database $fileStorageDb,
        Filesystem $filesystem,
        Registry $coreRegistry,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        MessageManager $messageManager,
        Json $serializer = null,
        UploaderFactory $uploaderFactory = null
    ) {
        parent::__construct($catalogProductOption, $eavConfig, $catalogProductType, $eventManager, $fileStorageDb, $filesystem, $coreRegistry, $logger,
            $productRepository, $serializer, $uploaderFactory);

        $this->messageManager = $messageManager;
    }

    /**
     * Check is virtual product
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    public function isVirtual($product)
    {
        return true;
    }

    /**
     * Check that product of this type has weight
     *
     * @return bool
     */
    public function hasWeight()
    {
        return false;
    }

    /**
     * Delete data specific for Warranty product type
     *
     * @param Product $product
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // @codingStandardsIgnoreStart
    public function deleteTypeSpecificData(Product $product)
    {
    }
    // @codingStandardsIgnoreEnd

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getSpecifyOptionMessage()
    {
        return __('A warranty product does not have the associated product.');
    }

    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);

        if (!$this->warrantyHasAssociatedProduct($buyRequest)) {
            $message = $this->getSpecifyOptionMessage();
            $this->messageManager->addErrorMessage($message);

            return $this->getSpecifyOptionMessage();
        }

        return $result;
    }

    /**
     * @param $buyRequest
     * @return bool
     */
    protected function warrantyHasAssociatedProduct($buyRequest)
    {
        return $buyRequest->getWarrantyProduct() && $buyRequest->getOriginalProduct();
    }
}
