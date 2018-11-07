<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Setup;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Mulberry\Warranty\Model\Product\Type as Warranty;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var CollectionFactory $collection
     */
    private $productCollection;

    /**
     * @var State $state
     */
    private $state;

    /**
     * @var Registry $registry
     */
    private $registry;

    /**
     * @var ProductRepositoryInterface $productRepository
     */
    private $productRepository;

    /**
     * @var ConsoleOutputInterface $output
     */
    private $output;

    public function __construct(
        CollectionFactory $productCollection,
        EavSetupFactory $eavSetupFactory,
        State $state,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        ConsoleOutputInterface $output
    ) {
        $this->productCollection = $productCollection;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->state = $state;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->output = $output;
    }

    /**
     * Remove data that was created during module installation and usage.
     *
     * @param SchemaSetupInterface|\Magento\Framework\Module\Setup $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
        }

        $isAreaChanged = false;

        if (!$this->registry->registry('isSecureArea')) {
            $this->registry->register('isSecureArea', true);
            $isAreaChanged = true;
        }

        $this->removeSystemConfigs($setup);
        $this->updateProductAttributes();
        $this->removeWarrantyProducts();

        if ($isAreaChanged) {
            $this->registry->unregister('isSecureArea');
        }
    }

    /**
     * Remove data within system config area
     *
     * @param SchemaSetupInterface $setup
     */
    private function removeSystemConfigs(SchemaSetupInterface $setup)
    {
        $defaultConnection = $setup->getConnection();

        $configTable = $setup->getTable('core_config_data');
        $defaultConnection->delete($configTable, "`path` LIKE 'mulberry_warranty/%'");
    }

    /**
     * Remove warranty product from "apply_to"
     */
    private function updateProductAttributes()
    {
        $eavSetup = $this->eavSetupFactory->create();

        $fieldList = [
            'price',
            'cost',
            'weight',
        ];

        /**
         * Make these attributes applicable to warranty products
         */
        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(Product::ENTITY, $field, 'apply_to')
            );

            if (in_array(Warranty::TYPE_ID, $applyTo)) {
                $applyTo = array_flip($applyTo);
                unset($applyTo[Warranty::TYPE_ID]);
                $applyTo = array_flip($applyTo);

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
     * Remove warranty product entities from Magento catalog
     */
    private function removeWarrantyProducts()
    {
        $collection = $this->productCollection->create();
        $collection->addAttributeToFilter('type_id', Warranty::TYPE_ID);

        try {
            foreach ($collection as $product) {
                $this->deleteWarrantyProduct($product);
            }
        } catch (StateException $e) {
            $this->output->writeln($e->getMessage());
        }
    }

    /**
     * @param Product $product
     */
    private function deleteWarrantyProduct(Product $product)
    {
        $product->delete();
    }
}
