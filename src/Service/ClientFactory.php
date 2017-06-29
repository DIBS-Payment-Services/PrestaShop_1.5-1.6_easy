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

namespace Invertus\Dibs\Service;

use Guzzle\Http\Client;
use Guzzle\Http\ClientInterface;
use Invertus\Dibs\Adapter\ConfigurationAdapter;

/**
 * Class ClientFactory
 *
 * @package Invertus\Dibs\Service
 */
class ClientFactory
{
    /**
     * @var ConfigurationAdapter
     */
    private $configurationAdapter;

    /**
     * @var string
     */
    private $testUrl;

    /**
     * @var string
     */
    private $prodUrl;

    /**
     * ClientFactory constructor.
     *
     * @param ConfigurationAdapter $configurationAdapter
     * @param string $testUrl
     * @param string $prodUrl
     */
    public function __construct(ConfigurationAdapter $configurationAdapter, $testUrl, $prodUrl)
    {
        $this->configurationAdapter = $configurationAdapter;
        $this->testUrl = $testUrl;
        $this->prodUrl = $prodUrl;
    }

    /**
     * Create new Guzzle client
     *
     * @return ClientInterface
     */
    public function createNew()
    {
        $isTestModeOn = (bool) $this->configurationAdapter->get('DIBS_TEST_MODE');
        $baseUrl = $isTestModeOn ? $this->testUrl : $this->prodUrl;
        $auhorizationKey = $isTestModeOn ?
            $this->configurationAdapter->get('DIBS_TEST_SECRET_KEY') :
            $this->configurationAdapter->get('DIBS_PROD_SECRET_KEY');

        $config = array(
            'request.options' => array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => $auhorizationKey,
                ),
            ),
        );

        $client = new Client($baseUrl, $config);

        return $client;
    }
}
