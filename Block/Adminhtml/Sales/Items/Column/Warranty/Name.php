<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Block\Adminhtml\Sales\Items\Column\Warranty;

use Magento\Sales\Block\Adminhtml\Items\Column\Name as CoreName;

/**
 * Sales Order warranty items name column renderer
 *
 * @package Mulberry\Warranty\Block\Adminhtml\Sales\Items\Column\Warranty
 */
class Name extends CoreName
{
    /**
     * Column label mapping for warranty options
     *
     * @var array
     */
    private $warrantyOptionColumns = [
        'service_type' => 'Service Type',
        'warranty_hash' => 'Warranty Hash',
        'duration_months' => 'Duration (Months)',
    ];

    /**
     * Extend options output with appropriate warranty information
     *
     * @return array
     */
    public function getOrderOptions()
    {
        $result = parent::getOrderOptions();

        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['info_buyRequest']['warranty_product'])) {
                $warrantyOptions = $options['info_buyRequest']['warranty_product'];
                $formattedWarrantyOptions = [];

                foreach ($this->warrantyOptionColumns as $optionCode => $optionLabel) {
                    $formattedWarrantyOptions[] = [
                        'label' => __($optionLabel),
                        'value' => $warrantyOptions[$optionCode],
                    ];
                }

                $result = array_merge($result, $formattedWarrantyOptions);
            }
        }

        return $result;
    }
}
