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
 * Class TabAdapter
 *
 * @package Invertus\DibsEasy\Adapter
 */
class TabAdapter
{
    /**
     * @param string $className
     *
     * @return int
     */
    public function getIdFromClassName($className)
    {
        return \Tab::getIdFromClassName($className);
    }
}
