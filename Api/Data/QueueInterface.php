<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */
declare(strict_types=1);

namespace Mulberry\Warranty\Api\Data;

interface QueueInterface
{
    const ENTITY_ID = 'entity_id';

    const STATUS_SYNCED = 'synced';
    const STATUS_FAILED = 'failed';
    const STATUS_SKIPPED = 'skipped';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return QueueInterface
     */
    public function setEntityId($entityId);

    /**
     * @param $orderId
     * @return mixed
     */
    public function setOrderId($orderId);

    /**
     * @return mixed
     */
    public function getOrderId();

    /**
     * @param $type
     * @return mixed
     */
    public function setActionType($type);

    /**
     * @return mixed
     */
    public function getActionType();

    /**
     * @param $status
     * @return mixed
     */
    public function setSyncStatus($status);

    /**
     * @return mixed
     */
    public function getSyncStatus();

    /**
     * @param $date
     * @return mixed
     */
    public function setSyncDate($date);

    /**
     * @return mixed
     */
    public function getSyncDate();
}
