<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item;
use Mulberry\Warranty\Api\ItemUpdaterInterface;

class ItemUpdater extends AbstractHelper implements ItemUpdaterInterface
{
    /**
     * @var Json $serializer
     */
    private $serializer;

    /**
     * @var ItemOptionHelper $itemOptionHelper
     */
    private $itemOptionHelper;

    /**
     * List of attributes to be displayed as "Additional Options" within warranty item
     *
     * @var array $warrantyAdditionalOptions
     */
    protected $warrantyAdditionalOptions = [
        'service_type',
        'duration_months',
    ];

    /**
     * ItemUpdater constructor.
     *
     * @param Context $context
     * @param Json $serializer
     * @param ItemOptionHelper $itemOptionHelper
     */
    public function __construct(
        Context $context,
        Json $serializer,
        ItemOptionHelper $itemOptionHelper
    ) {
        parent::__construct($context);

        $this->serializer = $serializer;
        $this->itemOptionHelper = $itemOptionHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function updateWarrantyProductName(Item $quoteItem): Item
    {
        if ($warrantyOptions = $this->itemOptionHelper->getWarrantyOption($quoteItem)) {
            $optionsInformation = $warrantyOptions->getData();

            if (isset($optionsInformation['original_product']['product_name'])) {
                $itemName = sprintf('Warranty - %s', $optionsInformation['original_product']['product_name']);
                $quoteItem->setName($itemName);
            }
        }

        return $quoteItem;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomWarrantyItemPrice(Item $warrantyQuoteItem, array $options = []): Item
    {
        $warrantyQuoteItem->setCustomPrice($options['warranty_product']['warranty_price']);
        $warrantyQuoteItem->setOriginalCustomPrice($options['warranty_product']['warranty_price']);
        $warrantyQuoteItem->getProduct()->setIsSuperMode(true);

        return $warrantyQuoteItem;
    }

    /**
     * {@inheritdoc}
     */
    public function addWarrantyItemOption(Product $warrantyProduct, array $options = []): Product
    {
        $warrantyProduct->addCustomOption(
            ItemOptionHelper::WARRANTY_INFORMATION_OPTION_CODE,
            $this->serializer->serialize($options)
        );

        return $warrantyProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function addAdditionalOptions(Product $warrantyProduct, array $options = []): Product
    {
        $additionalOptions = [];

        foreach ($options as $key => $value) {
            if (in_array($key, $this->warrantyAdditionalOptions)) {
                $label = ucwords(str_replace('_', ' ', $key));

                $additionalOptions[] = [
                    'label' => __($label),
                    'value' => $value,
                ];
            }
        }

        $warrantyProduct->addCustomOption(
            'additional_options',
            $this->serializer->serialize($additionalOptions)
        );

        return $warrantyProduct;
    }
}
