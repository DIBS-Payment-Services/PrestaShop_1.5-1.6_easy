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
 * Class ToolsAdapter
 *
 * @package Invertus\DibsEasy\Adapter
 */
class ToolsAdapter
{
    /**
     * @param mixed $data
     *
     * @return string
     */
    public function jsonEncode($data)
    {
        return \Tools::jsonEncode($data);
    }

    /**
     * @param string $data
     * @param bool $asArray
     *
     * @return array
     */
    public function jsonDecode($data, $asArray = true)
    {
        return \Tools::jsonDecode($data, $asArray);
    }

    /**
     * Copy file
     *
     * @param string $src
     * @param string $dest
     *
     * @return bool|int
     */
    public function copyFile($src, $dest)
    {
        return \Tools::copy($src, $dest);
    }

    /**
     * Delete given file
     *
     * @param string $src
     */
    public function deleteFile($src)
    {
        \Tools::deleteFile($src);
    }

    /**
     * @param string $file
     *
     * @return bool|mixed
     */
    public function fileGetContents($file)
    {
        return \Tools::file_get_contents($file);
    }
}
