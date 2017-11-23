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

namespace Invertus\DibsEasy\Util;

use Address;

class AddressChecksum
{
    /**
     * @param Address $address
     *
     * @return string
     */
    public function generateChecksum(Address $address)
    {
        $id = '';
        $id .= $address->alias;
        $id .= $address->address1;
        $id .= $address->address2;
        $id .= $address->postcode;
        $id .= $address->city;
        $id .= $address->id_country;
        $id .= $address->firstname;
        $id .= $address->lastname;
        $id .= $address->phone;
        $id .= $address->id_customer;

        return sha1($id);
    }
}
