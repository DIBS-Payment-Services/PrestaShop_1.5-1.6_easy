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

use Currency;
use Tools;

class PriceRoundAdapter
{
    public function roundPrice($price, $idCurrency)
    {
        $currency = new Currency($idCurrency);
        $currencyDecimals = (int) $currency->decimals * _PS_PRICE_DISPLAY_PRECISION_;

        $roundedPrice = Tools::ps_round($price, $currencyDecimals);

        return $roundedPrice;
    }
}
