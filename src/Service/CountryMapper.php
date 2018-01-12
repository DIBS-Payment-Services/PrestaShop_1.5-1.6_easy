<?php

namespace Invertus\DibsEasy\Service;

/**
 * Class CountryMapper
 * Maps Alpha-2 ISO codes with Alpha-3 ISO codes
 */
class CountryMapper
{
    const DEFAULT_COUNTRY = 'SWE';

    public function getIso2Code($iso3Code)
    {
        $mappings = $this->mappings();

        if (isset($mappings[$iso3Code])) {
            return $mappings[$iso3Code];
        }

        return $mappings[self::DEFAULT_COUNTRY];
    }

    public function mappings()
    {
        return array(
            'DNK' => 'DK',
            'NOR' => 'NO',
            'SWE' => 'SE',
            'ALB' => 'AL',
            'AND' => 'AD',
            'ARM' => 'AM',
            'AUT' => 'AT',
            'AZE' => 'AZ',
            'BEL' => 'BE',
            'BGR' => 'BG',
            'BIH' => 'BA',
            'BLR' => 'BY',
            'CHE' => 'CH',
            'CYP' => 'CY',
            'CZE' => 'CZ',
            'DEU' => 'DE',
            'ESP' => 'ES',
            'EST' => 'EE',
            'FIN' => 'FI',
            'FRA' => 'FR',
            'GBR' => 'GB',
            'GEO' => 'GE',
            'GRC' => 'GR',
            'HRV' => 'HR',
            'HUN' => 'HU',
            'IRL' => 'IE',
            'ISL' => 'IS',
            'ITA' => 'IT',
            'KAZ' => 'KZ',
            'LIE' => 'LI',
            'LTU' => 'LT',
            'LUX' => 'LU',
            'LVA' => 'LV',
            'MCO' => 'MC',
            'MDA' => 'MD',
            'MKD' => 'MK',
            'MLT' => 'MT',
            'MNE' => 'ME',
            'NLD' => 'NL',
            'POL' => 'PL',
            'PRT' => 'PT',
            'ROU' => 'RO',
            'RUS' => 'RU',
            'SMR' => 'SM',
            'SRB' => 'RS',
            'SVK' => 'SK',
            'SVN' => 'SI',
            'TUR' => 'TR',
            'UKR' => 'UA',
            'VAT' => 'VA',
        );
    }
}
