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

namespace Invertus\DibsEasy\Adapter;

/**
 * Class ConfigurationAdapter
 *
 * @package Invertus\DibsEasy\Adapter
 */
class ConfigurationAdapter
{
    /**
     * Set configuration value
     *
     * @param string $key
     * @param string|int $value
     *
     * @return bool
     */
    public function set($key, $value)
    {
        return \Configuration::updateValue($key, $value);
    }

    /**
     * Get configuration value
     *
     * @param string $key
     *
     * @return string
     */
    public function get($key)
    {
        return \Configuration::get($key);
    }

    /**
     * Get canceled order state ID
     *
     * @return int
     */
    public function getCanceledOrderStateId()
    {
        return (int) $this->get('DIBS_CANCELED_ORDER_STATE_ID');
    }

    /**
     * Get awaiting payment order state ID
     *
     * @return int
     */
    public function getAwaitingOrderStateId()
    {
        return (int) $this->get('DIBS_AWAITING_ORDER_STATE_ID');
    }

    /**
     * Get refunded payment order state ID
     *
     * @return int
     */
    public function getRefundedOrderStateId()
    {
        return (int) $this->get('DIBS_REFUNDED_ORDER_STATE_ID');
    }

    /**
     * Get completed payment order state ID
     *
     * @return int
     */
    public function getCompletedOrderStateId()
    {
        return (int) $this->get('DIBS_COMPLETED_ORDER_STATE_ID');
    }

    /**
     * Delete configuration by name
     *
     * @param string $key
     *
     * @return bool
     */
    public function remove($key)
    {
        return \Configuration::deleteByName($key);
    }
}
