<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2021 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace Mulberry\Warranty\Api\Model;

interface CrawlerInterface
{
    /**
     * Exports entire catalog, collecting product names, prices, images etc.
     *
     * @return array
     */
    public function exportProducts(): array;

    /**
     * Exports only single page of product catalog
     *
     * @param $page
     * @return array
     */
    public function exportProductsByPage($page): array;
}
