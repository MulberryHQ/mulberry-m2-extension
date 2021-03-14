<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
declare(strict_types=1);

namespace Mulberry\Warranty\Model\Processor;

use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Mulberry\Warranty\Api\QueueProcessorInterface;
use Mulberry\Warranty\Api\QueueRepositoryInterface;
use Mulberry\Warranty\Api\Rest\SendCartServiceInterface;
use Mulberry\Warranty\Api\Rest\SendOrderServiceInterface;
use Mulberry\Warranty\Model\Queue as QueueModel;
use Mulberry\Warranty\Model\QueueFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Queue implements QueueProcessorInterface
{
    /**
     * @var QueueRepositoryInterface $queueRepository
     */
    private $queueRepository;

    /**
     * @var QueueFactory $queueFactory
     */
    private $queueFactory;

    /**
     * @var SendOrderServiceInterface $sendOrderService
     */
    private $sendOrderService;

    /**
     * @var SendCartServiceInterface $sendCartService
     */
    private $sendCartService;

    /**
     * @var CollectionFactory $orderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param QueueResource $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        QueueRepositoryInterface $queueRepository,
        QueueFactory $queueFactory,
        SendOrderServiceInterface $sendOrderService,
        SendCartServiceInterface $sendCartService,
        CollectionFactory $orderCollectionFactory,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->queueRepository = $queueRepository;
        $this->queueFactory = $queueFactory;
        $this->sendOrderService = $sendOrderService;
        $this->sendCartService = $sendCartService;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @inheritdoc
     */
    public function addToQueue(OrderInterface $order, string $type): void
    {
        /**
         * @var $queueModel QueueModel
         */
        $queueModel = $this->queueFactory->create();
        $queueModel->setOrderId($order->getId());
        $queueModel->setActionType($type);

        $this->queueRepository->save($queueModel);
    }

    /**
     * @inheritdoc
     */
    public function orderHasWarrantyItems(OrderInterface $order): bool
    {
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() === Type::TYPE_ID) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function process(OrderInterface $order, $actionType): bool
    {
        $result = false;
        /**
         * @var $queue QueueModel
         */
        $queue = $this->queueRepository->getByOrderIdAndActionType($order->getId(), $actionType);

        if ($queue->getId()) {
            switch ($actionType) {
                case self::ACTION_TYPE_ORDER:
                    $processResult = $this->sendOrderService->sendOrder($order);
                    break;
                case self::ACTION_TYPE_CART:
                    $processResult = $this->sendCartService->sendCart($order);
                    break;
                default:
                    $processResult = ['status' => 'skipped'];
                    break;
            }

            $queue->setSyncStatus($processResult['status']);
            $queue->setSyncDate($this->dateTimeFactory->create()->gmtDate());
            $this->queueRepository->save($queue);
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
     * @return OrderCollection
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
     * @return OrderCollection
     */
    private function addActionTypeFilter(OrderCollection $collection, $actionType)
    {
        $collection->getSelect()->where(
            'mwq.action_type = ?',
            $actionType
        );

        return $this;
    }
}
