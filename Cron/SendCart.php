<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
declare(strict_types=1);

namespace Mulberry\Warranty\Cron;

use Mulberry\Warranty\Api\QueueProcessorInterface;
use Mulberry\Warranty\Model\Processor\Queue;
use Psr\Log\LoggerInterface;
use Magento\Framework\Event\Observer;
use Mulberry\Warranty\Api\Rest\SendOrderServiceInterface;

class SendCart
{
    /**
     * Process 20 "order" action type records in a single cron run
     */
    const BATCH_SIZE = 20;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var QueueProcessorInterface $queueProcessor
     */
    private $queueProcessor;

    /**
     * SendOrder constructor.
     *
     * @param LoggerInterface $logger
     * @param QueueProcessorInterface $queueProcessor
     */
    public function __construct(
        LoggerInterface $logger,
        QueueProcessorInterface $queueProcessor
    ) {
        $this->logger = $logger;
        $this->queueProcessor = $queueProcessor;
    }

    /**
     * @param Observer $observer
     */
    public function execute()
    {
        $collection = $this->queueProcessor->getCartsToExport();
        $collection->setPageSize(self::BATCH_SIZE)
            ->setCurPage(1);

        $this->logger->addInfo(
            __('Starting SendCart action processing. There are %1 records that will be processed', $collection->getSize())
        );

        foreach ($collection as $order) {
            $this->queueProcessor->process($order, Queue::ACTION_TYPE_CART);
        }

        $this->logger->addInfo('Cronjob SendCart is executed.');
    }
}
