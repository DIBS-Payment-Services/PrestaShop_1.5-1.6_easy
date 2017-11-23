<?php
/**
 * 2016 - 2017 Invertus, UAB
 *
 * NOTICE OF LICENSE
 *
 * This file is proprietary and can not be copied and/or distributed
 * without the express permission of INVERTUS, UAB
 *
 * @author    INVERTUS, UAB www.invertus.eu <support@invertus.eu>
 * @copyright Copyright (c) permanent, INVERTUS, UAB
 * @license   Addons PrestaShop license limitation
 *
 * International Registered Trademark & Property of INVERTUS, UAB
 */

namespace Invertus\DibsEasy\Service;

/**
 * Class ApiResponse
 *
 * @package Invertus\DibsEasy\Service
 */
class ApiResponse
{
    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var array Response body
     */
    private $body;

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * Check if response is successful
     *
     * @return bool
     */
    public function isSuccess()
    {
        return isset($this->statusCode) &&
            $this->statusCode >= 200 &&
            $this->statusCode < 300;
    }

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param array $body
     */
    public function setBody(array $body)
    {
        $this->body = $body;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getFromBody($key)
    {
        if (array_key_exists($key, $this->body)) {
            return $this->body[$key];
        }

        return null;
    }
}
