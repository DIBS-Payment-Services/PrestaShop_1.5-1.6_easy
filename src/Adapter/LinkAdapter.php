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

namespace Invertus\Dibs\Adapter;

/**
 * Class LinkAdapter
 *
 * @package Invertus\Dibs\Adapter
 */
class LinkAdapter
{
    /**
     * Get module FC link
     *
     * @param string $module
     * @param string $controller
     * @param array $params
     *
     * @return string
     */
    public function getModuleLink($module, $controller, array $params = array())
    {
        return \Context::getContext()->link->getModuleLink($module, $controller, $params, true);
    }
}
