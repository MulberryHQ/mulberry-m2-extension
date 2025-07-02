<?php

namespace Mulberry\Warranty\Console\Command;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Mulberry\Warranty\Api\QueueProcessorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCart extends Command
{
    private const INPUT_KEY_ORDER_ID = 'order_id';
    private const INPUT_KEY_INCREMENT_ID = 'increment_id';
    private const MESSAGE_SUCCESS = 'Success: %s';
    private const MESSAGE_ERROR = 'Error: %s';

    private OrderRepositoryInterface $orderRepository;
    private QueueProcessorInterface $queueProcessor;
    private State $state;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        QueueProcessorInterface $queueProcessor,
        State $state,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        string $name = null
    ) {
        parent::__construct($name);

        $this->orderRepository = $orderRepository;
        $this->queueProcessor = $queueProcessor;
        $this->state = $state;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Initialization of the command.
     */
    protected function configure()
    {
        $this->setName('mulberry:warranty:sync_cart');
        $this->setDescription('Re-sync the Magento post-purchase hook to Mulberry platform');
        $this->addOption(self::INPUT_KEY_ORDER_ID, 'o', InputOption::VALUE_REQUIRED, 'Magento Order ID (comma-separated for multiple)');
        $this->addOption(self::INPUT_KEY_INCREMENT_ID, 'i', InputOption::VALUE_REQUIRED, 'Magento Order Increment ID (comma-separated for multiple)');

        parent::configure();
    }

    /**
     * CLI command description.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->state->setAreaCode(Area::AREA_FRONTEND);

            $orderIds = $input->getOption(self::INPUT_KEY_ORDER_ID);
            $incrementIds = $input->getOption(self::INPUT_KEY_INCREMENT_ID);

            if (!$orderIds && !$incrementIds) {
                $output->writeln('<error>' . sprintf(self::MESSAGE_ERROR, __('Please provide either order_id or increment_id parameter')) . '</error>');
                return Cli::RETURN_FAILURE;
            }

            $orders = [];
            if ($orderIds) {
                $orderIdArray = array_map('trim', explode(',', $orderIds));
                $orders = $this->getOrdersByIds($orderIdArray);
            } elseif ($incrementIds) {
                $incrementIdArray = array_map('trim', explode(',', $incrementIds));
                $orders = $this->getOrdersByIncrementIds($incrementIdArray);
            }

            if (empty($orders)) {
                $identifier = $orderIds ?: $incrementIds;
                $output->writeln('<info>' . sprintf(self::MESSAGE_ERROR, __('No orders found with provided IDs: "%1"', $identifier)) . '</info>');
                return Cli::RETURN_FAILURE;
            }

            $successCount = 0;
            $errorCount = 0;

            foreach ($orders as $order) {
                try {
                    $this->queueProcessor->addToQueue($order, QueueProcessorInterface::ACTION_TYPE_CART, true);
                    if ($this->queueProcessor->process($order, QueueProcessorInterface::ACTION_TYPE_CART)) {
                        $output->writeln('<info>' . sprintf(self::MESSAGE_SUCCESS, __('Increment ID - %1', $order->getIncrementId())) . '</info>');
                        $successCount++;
                    } else {
                        $output->writeln('<error>' . sprintf(self::MESSAGE_ERROR, __('Failed to sync order with increment ID "%1"', $order->getIncrementId())) . '</error>');
                        $errorCount++;
                    }
                } catch (\Exception $e) {
                    $output->writeln('<error>' . sprintf(self::MESSAGE_ERROR, __('Error processing order "%1": %2', $order->getIncrementId(), $e->getMessage())) . '</error>');
                    $errorCount++;
                }
            }

            $output->writeln(__('Processed %1 orders: %2 successful, %3 failed', count($orders), $successCount, $errorCount));
        } catch (\Exception $e) {
            $output->writeln('<error>' . sprintf(self::MESSAGE_ERROR, $e->getMessage()) . '</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param array $orderIds
     * @return OrderInterface[]
     */
    private function getOrdersByIds(array $orderIds): array
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::ENTITY_ID, $orderIds, 'in')
            ->create();

        return $this->orderRepository->getList($criteria)->getItems();
    }

    /**
     * @param array $incrementIds
     * @return OrderInterface[]
     */
    private function getOrdersByIncrementIds(array $incrementIds): array
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::INCREMENT_ID, $incrementIds, 'in')
            ->create();

        return $this->orderRepository->getList($criteria)->getItems();
    }
}
