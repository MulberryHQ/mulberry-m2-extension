<?php

namespace Mulberry\Warranty\Console\Command;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Console\Cli;
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
        $this->addOption(self::INPUT_KEY_ORDER_ID, 'o', InputOption::VALUE_REQUIRED, 'Magento Order Increment ID');

        parent::configure();
    }

    /**
     * CLI command description.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->state->setAreaCode(Area::AREA_FRONTEND);

            $incrementId = $input->getOption(self::INPUT_KEY_ORDER_ID);
            $order = $this->getOrderByIncrementId($incrementId);

            if (!$order) {
                $output->writeln('<info>' . sprintf(self::MESSAGE_ERROR, __('Order with increment ID "%1" is not found.', $incrementId)) . '</info>');

                return Cli::RETURN_FAILURE;
            }

            if ($this->queueProcessor->process($order, QueueProcessorInterface::ACTION_TYPE_CART)) {
                $output->writeln('<info>' . sprintf(self::MESSAGE_SUCCESS, __('Increment ID - %1', $order->getIncrementId())) . '</info>');
            } else {
                $output->writeln('<error>' . sprintf(self::MESSAGE_ERROR,
                        __('There was an error with the order sync, please see mulberry_warranty_queue.log file for more information')) . '</error>');
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . sprintf(self::MESSAGE_ERROR, $e->getMessage()) . '</error>');

            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param $incrementId
     * @return OrderInterface|null
     */
    private function getOrderByIncrementId($incrementId): ?OrderInterface
    {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter(OrderInterface::INCREMENT_ID, $incrementId)
            ->create();
        $orders = $this->orderRepository->getList($criteria)->getItems();

        return count($orders)? $orders[0] : null;
    }
}
