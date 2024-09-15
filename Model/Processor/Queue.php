<?php
declare(strict_types=1);
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2024 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Model\Processor;

use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Mulberry\Warranty\Api\Data\QueueInterface;
use Mulberry\Warranty\Api\QueueProcessorInterface;
use Mulberry\Warranty\Api\QueueRepositoryInterface;
use Mulberry\Warranty\Api\Rest\SendCartServiceInterface;
use Mulberry\Warranty\Api\Rest\SendOrderServiceInterface;
use Mulberry\Warranty\Model\Queue as QueueModel;
use Mulberry\Warranty\Model\QueueFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Psr\Log\LoggerInterface;

class Queue implements QueueProcessorInterface
{
    /**
     * @var QueueRepositoryInterface $queueRepository
     */
    private QueueRepositoryInterface $queueRepository;

    /**
     * @var QueueFactory $queueFactory
     */
    private QueueFactory $queueFactory;

    /**
     * @var SendOrderServiceInterface $sendOrderService
     */
    private SendOrderServiceInterface $sendOrderService;

    /**
     * @var SendCartServiceInterface $sendCartService
     */
    private SendCartServiceInterface $sendCartService;

    /**
     * @var CollectionFactory $orderCollectionFactory
     */
    private CollectionFactory $orderCollectionFactory;

    /**
     * @var DateTimeFactory $dateTimeFactory
     */
    private DateTimeFactory $dateTimeFactory;

    /**
     * @var LoggerInterface $logger
     */
    private LoggerInterface $logger;

    /**
     * Queue constructor.
     * @param QueueRepositoryInterface $queueRepository
     * @param QueueFactory $queueFactory
     * @param SendOrderServiceInterface $sendOrderService
     * @param SendCartServiceInterface $sendCartService
     * @param CollectionFactory $orderCollectionFactory
     * @param DateTimeFactory $dateTimeFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        QueueRepositoryInterface $queueRepository,
        QueueFactory $queueFactory,
        SendOrderServiceInterface $sendOrderService,
        SendCartServiceInterface $sendCartService,
        CollectionFactory $orderCollectionFactory,
        DateTimeFactory $dateTimeFactory,
        LoggerInterface $logger
    ) {
        $this->queueRepository = $queueRepository;
        $this->queueFactory = $queueFactory;
        $this->sendOrderService = $sendOrderService;
        $this->sendCartService = $sendCartService;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function addToQueue(OrderInterface $order, string $type, bool $force = false): bool
    {
        /**
         * Skip this when order is not saved yet
         */
        if (!$order->getId()) {
            return false;
        }

        /**
         * @var $queueModel QueueModel
         */
        $queueModel = $this->queueRepository->getByOrderIdAndActionType($order->getId(), $type);

        /**
         * Skip adding record to the queue if no "force" flag is set.
         */
        if ($queueModel->getId() && !$force) {
            return false;
        }

        $queueModel->setOrderId($order->getId());
        $queueModel->setActionType($type);

        /**
         * Reset sync status field if order is force-added to the queue
         */
        if ($force) {
            $queueModel->setSyncStatus(null);
            $queueModel->setSyncDate(null);
        }

        $this->queueRepository->save($queueModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function process(OrderInterface $order, $actionType): bool
    {
        try {
            /**
             * @var $queue QueueModel
             */
            $queue = $this->queueRepository->getByOrderIdAndActionType($order->getId(), $actionType);

            if (!$queue->getId()) {
                $this->logger->error(__('Order with increment ID "%1" is not queued for action type "%2"', $order->getIncrementId(), $actionType));

                return false;
            }

            switch ($actionType) {
                case self::ACTION_TYPE_ORDER:
                    $result = $this->sendOrderService->sendOrder($order);
                    break;
                case self::ACTION_TYPE_CART:
                    $result = $this->sendCartService->sendCart($order);
                    break;
                default:
                    $result = [
                        'status' => QueueInterface::STATUS_SKIPPED,
                        'response' => __('Invalid action type for order "#%1"', $order->getIncrementId())
                    ];
                    break;
            }

            $queue->setSyncStatus($result['status']);
            $queue->setSyncDate($this->dateTimeFactory->create()->gmtDate());
            $this->queueRepository->save($queue);

            if ($result['status'] !== QueueInterface::STATUS_SYNCED) {
                $this->logger->error(__('Incorrect response sync status for order "%1", error: "%2', $order->getIncrementId(), json_encode($result)));
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error(__('There was an error when processing order %1, error: %2', $order->getIncrementId(), $e->getMessage()));
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getOrdersToExport(): OrderCollection
    {
        $collection = $this->getOrderCollection();
        $this->addSyncStatusFilter($collection, null);
        $this->addActionTypeFilter($collection, self::ACTION_TYPE_ORDER);

        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function getCartsToExport(): OrderCollection
    {
        $collection = $this->getOrderCollection();
        $this->addSyncStatusFilter($collection, null);
        $this->addActionTypeFilter($collection, self::ACTION_TYPE_CART);
        $this->excludePendingAndSkippedOrders($collection);

        return $collection;
    }

    /**
     * @return OrderCollection
     */
    private function getOrderCollection(): OrderCollection
    {
        $orderCollection = $this->orderCollectionFactory->create();
        $this->joinQueueToOrderCollection($orderCollection);

        return $orderCollection;
    }

    /**
     * @param $collection
     * @return mixed
     */
    private function joinQueueToOrderCollection(OrderCollection $collection)
    {
        $collection->getSelect()->joinLeft(
            ['mwq' => 'mulberry_warranty_queue'],
            'main_table.entity_id = mwq.order_id',
            ['action_type', 'sync_status', 'sync_date']
        );

        return $this;
    }

    /**
     * @param OrderCollection $collection
     * @param $syncstatus
     * @return $this
     */
    private function addSyncStatusFilter(OrderCollection $collection, $syncstatus)
    {
        $collection->getSelect()->where(
            $syncstatus === null ? 'mwq.sync_status IS NULL' : 'mwq.sync_status = ?',
            $syncstatus
        );

        return $this;
    }

    /**
     * Filter order collection by the action type
     *
     * @param OrderCollection $collection
     * @param $actionType
     * @return $this
     */
    private function addActionTypeFilter(OrderCollection $collection, $actionType)
    {
        $collection->getSelect()->where(
            'mwq.action_type = ?',
            $actionType
        );

        return $this;
    }

    /**
     * @param OrderCollection $collection
     * @return $this
     */
    private function excludePendingAndSkippedOrders(OrderCollection $collection)
    {
        $collection->getSelect()->joinLeft(
            ['mwq_order' => 'mulberry_warranty_queue'],
            'mwq.order_id = mwq_order.order_id AND mwq_order.action_type = "order"',
            ['order_sync_status' => 'sync_status']
        );

        $collection->getSelect()->where(
            'mwq_order.sync_status IN (?)',
            [QueueInterface::STATUS_SKIPPED, QueueInterface::STATUS_SYNCED]
        );

        return $this;
    }
}
