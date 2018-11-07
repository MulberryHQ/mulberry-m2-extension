<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Dmitrijs Sitovs <info@scandiweb.com / dmitrijssh@scandiweb.com / dsitovs@gmail.com>
 * @copyright Copyright (c) 2018 Scandiweb, Ltd (http://scandiweb.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Plugin;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Serialize\Serializer\Json;

class ConfigurablePlugin
{
    /**
     * @var Json $serializer
     */
    private $serializer;

    /**
     * ConfigurablePlugin constructor.
     *
     * @param Json $serializer
     */
    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Configurable $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetJsonConfig(Configurable $subject, $result)
    {
        $config = $this->serializer->unserialize($result);

        $config['simple_skus'] = [];

        foreach ($subject->getAllowProducts() as $simpleProduct) {
            $config['simple_skus'][$simpleProduct->getId()] = $simpleProduct->getSku();
        }

        return $this->serializer->serialize($config);
    }
}
