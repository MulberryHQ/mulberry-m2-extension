<?php

namespace Mulberry\Warranty\Console\Command;

use Magento\Sales\Api\OrderRepositoryInterface;
use Mulberry\Warranty\Api\QueueProcessorInterface;
use Mulberry\Warranty\Model\Processor\Queue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunOrder extends Command
{
    public const INPUT_KEY_ORDER_ID = 'order_id';
    public const INPUT_KEY_ACTION_TYPE = 'type';

    /**
     * Success Message
     */
    const MESSAGE_SUCCESS = 'Success: %s';

    /**
     * Error message
     */
    const MESSAGE_ERROR = 'Error: %s';

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        QueueProcessorInterface $queueProcessor,
        string $name = null
    ) {
        parent::__construct($name);

        $this->orderRepository = $orderRepository;
        $this->queueProcessor = $queueProcessor;
    }

    /**
     * Initialization of the command.
     */
    protected function configure()
    {
        $this->setName('mulberry:warranty:sync_order');
        $this->setDescription('Re-sync the Magento order to Mulberry platform');
        $this->addOption(self::INPUT_KEY_ORDER_ID, 'o', InputOption::VALUE_REQUIRED, 'Magento Order ID');
        $this->addOption(self::INPUT_KEY_ACTION_TYPE, 't', InputOption::VALUE_REQUIRED, 'Action Type');

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
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        try {
            $orderId = $input->getOption(self::INPUT_KEY_ORDER_ID);
            $actionType = $input->getOption(self::INPUT_KEY_ACTION_TYPE);

            $order = $this->orderRepository->get($orderId);

            if ($this->queueProcessor->process($order, $actionType)) {
                $output->writeln('<info>' . sprintf(self::MESSAGE_SUCCESS, '') . '</info>');
            } else {
                $output->writeln('<error>' . sprintf(self::MESSAGE_ERROR,
                        __('There was an error with the order sync')) . '</error>');
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . sprintf(self::MESSAGE_ERROR, $e->getMessage()) . '</error>');
        }
    }
}
