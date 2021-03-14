<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
declare(strict_types=1);

namespace Mulberry\Warranty\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Mulberry\Warranty\Api\Data\QueueInterface;
use Mulberry\Warranty\Api\Data\QueueInterfaceFactory;
use Mulberry\Warranty\Model\ResourceModel\Queue as QueueResource;
use Mulberry\Warranty\Model\ResourceModel\Queue\Collection;

class Queue extends AbstractModel implements QueueInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'mulberry_warranty_queue';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param QueueResource $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        QueueResource $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param $orderId
     * @return mixed|void
     */
    public function setOrderId($orderId)
    {
        $this->setData('order_id', $orderId);
    }

    /**
     * @return array|mixed|null
     */
    public function getOrderId()
    {
        return $this->getData('order_id');
    }

    /**
     * @param $type
     * @return mixed|void
     */
    public function setActionType($type)
    {
        $this->setData('action_type', $type);
    }

    /**
     * @return array|mixed|null
     */
    public function getActionType()
    {
        return $this->getData('action_type');
    }

    /**
     * @param $status
     * @return mixed|void
     */
    public function setSyncStatus($status)
    {
        $this->setData('sync_status', $status);
    }

    /**
     * @return array|mixed|null
     */
    public function getSyncStatus()
    {
        return $this->getData('sync_status');
    }

    /**
     * @param $date
     * @return mixed|void
     */
    public function setSyncDate($date)
    {
        $this->setData('sync_date', $date);
    }

    /**
     * @return array|mixed|null
     */
    public function getSyncDate()
    {
        return $this->getData('sync_date');
    }
}
