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
use Magento\CatalogInventory\Model\Stock\ItemFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Mulberry\Warranty\Model\Product\Type as Warranty;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\State;

class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory $eavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ItemFactory $stockItemFactory
     */
    private $stockItemFactory;

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
     * InstallData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param ItemFactory $stockItemFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ProductFactory $productFactory
     * @param State $state
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ItemFactory $stockItemFactory,
        ProductRepositoryInterface $productRepository,
        ProductFactory $productFactory,
        State $state
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->stockItemFactory = $stockItemFactory;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->state = $state;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
        }

        $this->updateAttributes($setup);
        $this->createWarrantyProduct();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function updateAttributes(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $fieldList = [
            'price',
        ];

        /**
         * Make these attributes applicable to warranty products
         */
        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(Product::ENTITY, $field, 'apply_to')
            );

            if (!in_array(Warranty::TYPE_ID, $applyTo)) {
                $applyTo[] = Warranty::TYPE_ID;

                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
        }
    }

    /**
     * Create warranty product placeholder that will be used to store warranty information
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function createWarrantyProduct()
    {
        $product = $this->productFactory->create();
        $product->setTypeId(Warranty::TYPE_ID)
            ->setSku('mulberry-warranty-product')
            ->setName('Mulberry Warranty Product')
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
