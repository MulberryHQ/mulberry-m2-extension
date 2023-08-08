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

class SendCart
{
    /**
     * Process 20 "cart" action type records in a single cron run
     */
    const BATCH_SIZE = 100;

    private LoggerInterface $logger;
    private QueueProcessorInterface $queueProcessor;

    /**
     * SendCart constructor.
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
     * @return void
     */
    public function execute()
    {
        $collection = $this->queueProcessor->getCartsToExport();
        $collection->setPageSize(self::BATCH_SIZE)
            ->setCurPage(1);

        $this->logger->info(
            __('Starting SendCart action processing. There are %1 records that will be processed', $collection->getSize())
        );

        foreach ($collection as $order) {
            try {
                $this->queueProcessor->process($order, QueueProcessorInterface::ACTION_TYPE_CART);
            } catch (\Exception $e) {
                $this->logger->error(__('There was an error when processing "cart" action for order with id "%1", error: %2', $order->getId(), $e->getMessage()));
            }
        }

        $this->logger->info('Cronjob SendCart is finished.');
    }
}
