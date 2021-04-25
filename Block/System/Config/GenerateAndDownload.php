<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Mulberry\Warranty\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Exception\LocalizedException;

class GenerateAndDownload extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Mulberry_Warranty::system/config/generateAndDownload.phtml';

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for generate and download button
     *
     * @return string
     */
    public function getGenerateAndDownloadAjaxUrl()
    {
        return $this->getUrl('mulberry_warranty/system_config/generate');
    }

    /**
     * Return ajax url for download button
     *
     * @return string
     */
    public function getDownloadAjaxUrl()
    {
        return $this->getUrl('mulberry_warranty/system_config/download');
    }

    /**
     * Generate collect button html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getGenerateAndDownloadButtonHtml()
    {
        $button = $this->getLayout()->createBlock(Button::class)->setData(
            [
                'id' => 'generate_and_download_button',
                'label' => __('Generate and Download'),
            ]
        );

        return $button->toHtml();
    }

    /**
     * Generate collect button html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getDownloadButtonHtml()
    {
        $button = $this->getLayout()->createBlock(Button::class)->setData(
            [
                'id' => 'download_button',
                'label' => __('Download'),
            ]
        );

        return $button->toHtml();
    }
}
