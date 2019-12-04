<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item;
use Mulberry\Warranty\Api\ItemOptionInterface;
use Mulberry\Warranty\Api\Rest\WarrantyServiceInterface;

class ItemOptionHelper extends AbstractHelper implements ItemOptionInterface
{
    /**
     * Warranty specific item information is stored under this code within quote_item_option table
     */
    const WARRANTY_INFORMATION_OPTION_CODE = 'warranty_information';

    /**
     * @var Json $serializer
     */
    private $serializer;

    /**
     * @var WarrantyServiceInterface $warrantyService
     */
    private $warrantyService;

    /**
     * @var array $warrantyInformationCache
     */
    private $warrantyInformationCache = [];

    /**
     * ItemOptionHelper constructor.
     *
     * @param Context $context
     * @param Json $serializer
     * @param WarrantyServiceInterface $warrantyService
     */
    public function __construct(
        Context $context,
        Json $serializer,
        WarrantyServiceInterface $warrantyService
    ) {
        parent::__construct($context);

        $this->serializer = $serializer;
        $this->warrantyService = $warrantyService;
    }

    /**
     * {@inheritdoc}
     */
    public function getWarrantyOption(Item $quoteItem): DataObject
    {
        $option = $quoteItem->getOptionByCode(self::WARRANTY_INFORMATION_OPTION_CODE);
        $data = $option ? $this->serializer->unserialize($option->getValue()) : [];

        return new DataObject($data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareWarrantyOption(Item $originalQuoteItem, string $warrantyHash): array
    {
        return [
            'warranty_product' => $this->prepareWarrantyInformation($warrantyHash),
            'original_product' => $this->prepareProductInformation($originalQuoteItem),
        ];
    }

    /**
     * Prepare warranty product information
     *
     * @param $warrantyHash
     *
     * @return array
     */
    public function prepareWarrantyInformation($warrantyHash): array
    {
        if (!array_key_exists($warrantyHash, $this->warrantyInformationCache)) {
            if ($warrantyInfo = $this->warrantyService->getWarrantyByHash($warrantyHash)) {
                $this->warrantyInformationCache[$warrantyHash] = $warrantyInfo;
            }
        }

        return $this->warrantyInformationCache[$warrantyHash];
    }

    /**
     * Fetch original product information and save it within warranty product for further processing
     *
     * @param Item $originalQuoteItem
     *
     * @return array
     */
    private function prepareProductInformation(Item $originalQuoteItem): array
    {
        $originalProductBuyRequest = $originalQuoteItem->getBuyRequest();

        return [
            'product' => $originalProductBuyRequest->getProduct(),
            'product_sku' => $originalQuoteItem->getSku(),
            'selected_configurable_option' => $originalProductBuyRequest->getSelectedConfigurableOption(),
        ];
    }
}
