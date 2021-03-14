<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mulberry\Warranty\Api\Data;

interface QueueInterface
{
    const ENTITY_ID = 'entity_id';

    const STATUS_SYNCED = 'synced';
    const STATUS_FAILED = 'failed';

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
