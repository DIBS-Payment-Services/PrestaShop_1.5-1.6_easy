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

class CardDetails
{
    /**
     * @var string
     */
    private $maskedPan;

    /**
     * @var string
     */
    private $expirityDate;

    /**
     * @return string
     */
    public function getMaskedPan()
    {
        return (string) $this->maskedPan;
    }

    /**
     * @param string $maskedPan
     */
    public function setMaskedPan($maskedPan)
    {
        $this->maskedPan = $maskedPan;
    }

    /**
     * @return string
     */
    public function getExpirityDate()
    {
        return $this->expirityDate;
    }

    /**
     * @param string $expirityDate
     */
    public function setExpirityDate($expirityDate)
    {
        $this->expirityDate = $expirityDate;
    }
}
