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

namespace Invertus\DibsEasy\Result;

class Summary
{
    /**
     * @var float
     */
    private $reservedAmount;

    /**
     * @var float
     */
    private $chargedAmount;

    /**
     * @return float
     */
    public function getReservedAmount()
    {
        return $this->reservedAmount;
    }

    /**
     * @return float
     */
    public function getChargedAmount()
    {
        return $this->chargedAmount;
    }

    /**
     * @param float $reservedAmount
     */
    public function setReservedAmount($reservedAmount)
    {
        $this->reservedAmount = $reservedAmount;
    }

    /**
     * @param float $chargedAmount
     */
    public function setChargedAmount($chargedAmount)
    {
        $this->chargedAmount = $chargedAmount;
    }

}
