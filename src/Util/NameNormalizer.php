<?php

namespace Invertus\DibsEasy\Util;

class NameNormalizer
{
    public function normalize($name)
    {
        $normalizedName = str_replace(array('\'', '"', '<', '>', '&'), '', $name);

        return $normalizedName;
    }
}
