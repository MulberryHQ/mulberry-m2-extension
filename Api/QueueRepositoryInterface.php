<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
declare(strict_types=1);

namespace Mulberry\Warranty\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mulberry\Warranty\Api\Data\QueueInterface;
use Mulberry\Warranty\Api\Data\QueueSearchResultsInterface;

interface QueueRepositoryInterface
{
    /**
     * Save Queue
     * @param QueueInterface $queue
     * @return QueueInterface
     * @throws LocalizedException
     */
    public function save(QueueInterface $queue);

    /**
     * Retrieve Queue
     * @param string $entityId
     * @return QueueInterface
     * @throws LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve queue entry by Magento order ID & action type
     *
     * @param $orderId
     * @param $actionType
     * @return mixed
     */
    public function getByOrderIdAndActionType($orderId, $actionType);

    /**
     * Delete Queue
     * @param QueueInterface $queue
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(QueueInterface $queue);

    /**
     * Delete Queue by ID
     * @param string $entityId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($entityId);
}
