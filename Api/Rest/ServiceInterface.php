<?php
/**
 * @category Mulberry
 * @package Mulberry\Warranty
 * @author Mulberry <support@getmulberry.com>
 * @copyright Copyright (c) 2019 Mulberry Technology Inc., Ltd (http://www.getmulberry.com)
 * @license http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace Mulberry\Warranty\Api\Rest;

interface ServiceInterface
{
    /**
     * post
     */
    const POST = 'post';

    /**
     * get
     */
    const GET = 'get';

    /**
     * put
     */
    const PUT = 'put';

    /**
     * patch
     */
    const PATCH = 'patch';

    /**
     * delete
     */
    const DELETE = 'delete';

    /**
     * @param $url
     * @param string $data
     * @param string $method
     * @return array
     */
    public function makeRequest($url, $data = '', $method = ServiceInterface::GET): array;

    /**
     * @param string      $header
     * @param string|null $value
     * @return mixed
     */
    public function setHeader($header, $value = null);
}
