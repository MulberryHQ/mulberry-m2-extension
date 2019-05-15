<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Observer;

use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class GenerateOrderUuid implements ObserverInterface
{
    /**
     * @var IdentityGeneratorInterface
     */
    private $identityGenerator;

    /**
     * GenerateOrderUuid constructor.
     * @param IdentityGeneratorInterface $identityGenerator
     */
    public function __construct(IdentityGeneratorInterface $identityGenerator)
    {
        $this->identityGenerator = $identityGenerator;
    }

    /**
     * Generate Mulberry order identifier
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /**
         * @var OrderInterface $order
         */
        $order = $observer->getEvent()->getOrder();

        $order->setOrderIdentifier($this->identityGenerator->generateId());
    }
}
