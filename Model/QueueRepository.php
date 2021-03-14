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

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mulberry\Warranty\Api\Data\QueueInterface;
use Mulberry\Warranty\Api\QueueRepositoryInterface;
use Mulberry\Warranty\Model\QueueFactory;
use Mulberry\Warranty\Model\ResourceModel\Queue as ResourceQueue;

class QueueRepository implements QueueRepositoryInterface
{
    /**
     * @var ResourceQueue $resource
     */
    protected $resource;

    /**
     * @var QueueFactory $queueFactory
     */
    protected $queueFactory;

    /**
     * @var ResourceQueue\CollectionFactory $collectionFactory
     */
    private $collectionFactory;

    /**
     * @param ResourceQueue $resource
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        ResourceQueue $resource,
        QueueFactory $queueFactory,
        ResourceQueue\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->queueFactory = $queueFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(QueueInterface $queue)
    {
        try {
            $this->resource->save($queue);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the queue: %1',
                $exception->getMessage()
            ));
        }

        return $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function get($queueId)
    {
        $queue = $this->queueFactory->create();
        $this->resource->load($queue, $queueId);

        if (!$queue->getId()) {
            throw new NoSuchEntityException(__('Queue with id "%1" does not exist.', $queueId));
        }

        return $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function getByOrderIdAndActionType($orderId, $actionType) : Queue
    {
        /**
         * @var $collection ResourceQueue\Collection
         */
        $collection = $this->collectionFactory->create();

        $collection->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('action_type', $actionType);

        return $collection->getSize() ? $collection->getFirstItem() : $this->queueFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(QueueInterface $queue)
    {
        try {
            $queueModel = $this->queueFactory->create();
            $this->resource->load($queueModel, $queue->getEntityId());
            $this->resource->delete($queueModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Queue: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($queueId)
    {
        return $this->delete($this->get($queueId));
    }
}

