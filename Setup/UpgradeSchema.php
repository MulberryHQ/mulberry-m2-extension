<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Sales\Model\Order;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            $this->extendOrderTable($setup);
            $this->extendOrderGridTable($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add order_identifier column to order table
     *
     * @param SchemaSetupInterface $setup
     */
    private function extendOrderTable(SchemaSetupInterface $setup): void
    {
        /**
         * Add data to order table
         */
        if ($setup->getConnection()->tableColumnExists($setup->getTable('sales_order'), 'order_identifier')) {
            $setup->getConnection()->dropColumn($setup->getTable('sales_order'), 'order_identifier');
        }

        /**
         * @var SalesSetup $salesSetup
         */
        $salesSetup = $this->salesSetupFactory->create();

        $salesSetup->addAttribute(Order::ENTITY, 'order_identifier', [
            'type' => Table::TYPE_TEXT,
            'length' => 255,
            'visible' => false,
            'nullable' => true,
            'comment' => 'Order Identifier',
        ]);
    }

    /**
     * Add order_identifier column to order grid table
     *
     * @param SchemaSetupInterface $setup
     */
    private function extendOrderGridTable(SchemaSetupInterface $setup): void
    {
        if ($setup->getConnection()->tableColumnExists($setup->getTable('sales_order_grid'), 'order_identifier')) {
            $setup->getConnection()->dropColumn($setup->getTable('sales_order_grid'), 'order_identifier');
        }

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_grid'),
            'order_identifier',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'visible' => false,
                'nullable' => true,
                'comment' => 'Order Identifier',
            ]
        );
    }
}
