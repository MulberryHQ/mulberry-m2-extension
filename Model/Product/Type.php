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
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class Type extends AbstractType
{
    const TYPE_ID = 'warranty';

    /**
     * @var MessageManager $messageManager
     */
    private $messageManager;

    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        UploaderFactory $uploaderFactory = null,
        MessageManager $messageManager
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
