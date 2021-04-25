<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Mulberry\Warranty\Console\Command;

use Mulberry\Warranty\Model\Crawler;
use InvalidArgumentException;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Store\Model\Store;

class Crawl extends Command
{
    /**
     * CLI command name
     */
    const NAME = 'mulberry:warranty:generate-feed';

    /**
     * CLI command description
     */
    const DESCRIPTION = 'Crawls product catalog and gathers data';

    /**
     * Success Message
     */
    const MESSAGE_SUCCESS = 'Crawled %s Products';

    /**
     * Error message
     */
    const MESSAGE_ERROR = 'Error: %s';

    /**
     * @var State
     */
    private $state;

    private $crawler;

    /**
     * RebuildCommand constructor
     *
     * @param State $state
     * @param Crawler $crawler
     */
    public function __construct(
        State $state,
        Crawler $crawler
    ) {
        $this->state = $state;
        $this->crawler = $crawler;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription(self::DESCRIPTION);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        }

        try {
            $crawledData = $this->crawlAll();
            $count = count($crawledData);

            $output->writeln('<info>' . sprintf(self::MESSAGE_SUCCESS, $count) . '</info>');
        } catch (Exception $e) {
            $output->writeln('<error>' . sprintf(self::MESSAGE_ERROR, $e->getMessage()) . '</error>');
        }
    }

    /**
     * Gather catalog product data
     *
     * @return array
     */
    private function crawlAll(): array
    {
        return $this->crawler->exportProducts();
    }
}
