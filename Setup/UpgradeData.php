<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Mulberry\Warranty\Model\Product\Type as Warranty;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\State;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var array $warrantyProductSkus
     */
    private $warrantyProductSkus = array(
        'mulberry-warranty-12-months' => 'Mulberry Warranty Product - 12 Months',
        'mulberry-warranty-24-months' => 'Mulberry Warranty Product - 24 Months',
        'mulberry-warranty-36-months' => 'Mulberry Warranty Product - 36 Months',
        'mulberry-warranty-48-months' => 'Mulberry Warranty Product - 48 Months',
        'mulberry-warranty-60-months' => 'Mulberry Warranty Product - 60 Months',
    );

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductFactory $productFactory
     */
    private $productFactory;

    /**
     * @var State $state
     */
    private $state;

    /**
     * UpgradeData constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param ProductFactory $productFactory
     * @param State $state
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductFactory $productFactory,
        State $state
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->state = $state;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
        }

        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            $this->createWarrantyProducts();
        }
    }

    /**
     * Create warranty product placeholder that will be used to store warranty information
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function createWarrantyProducts()
    {
        foreach ($this->warrantyProductSkus as $sku => $name) {
            $product = $this->productFactory->create();

            if (!$product->loadByAttribute('sku', $sku)) {
                $product->setTypeId(Warranty::TYPE_ID)
                    ->setSku($sku)
                    ->setName($name)
                    ->setStatus(Status::STATUS_ENABLED)
                    ->setVisibility(Product\Visibility::VISIBILITY_IN_CATALOG)
                    ->setAttributeSetId(4)
                    ->setTaxClassId(0)
                    ->setPrice(10.00)
                    ->setStockData([
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 0,
                        'is_in_stock' => 1,
                        'qty' => 0,
                        'is_qty_decimal' => 0,
                    ]);

                $this->productRepository->save($product);
            }
        }
    }
}
