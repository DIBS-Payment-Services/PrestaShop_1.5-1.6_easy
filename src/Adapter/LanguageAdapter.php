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
 * Class LanguageAdapter
 *
 * @package Invertus\DibsEasy\Adapter
 */
class LanguageAdapter
{
    /**
     * Get array of language IDs
     *
     * @param bool $active
     * @param int|bool $idShop
     *
     * @return array|int[]
     */
    public function getIDs($active = true, $idShop = false)
    {
        $languages = \Language::getLanguages($active, $idShop);

        $ids = array_map(function ($lang) {
            return (int) $lang['id_lang'];
        }, $languages);

        return $ids;
    }

    /**
     * Get language ISO codes
     *
     * @return array
     */
    public function getIsoCodes()
    {
        $langs = \Language::getIsoIds(true);
        $isoCodes = array_map(function ($lang) {
            return $lang['iso_code'];
        }, $langs);

        return $isoCodes;
    }
}
