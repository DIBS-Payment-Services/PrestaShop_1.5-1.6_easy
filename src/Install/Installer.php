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

namespace Invertus\DibsEasy\Install;

use Address;
use Country;
use Db;
use Exception;
use Invertus\DibsEasy\Adapter\ConfigurationAdapter;
use Invertus\DibsEasy\Adapter\LanguageAdapter;
use Invertus\DibsEasy\Adapter\TabAdapter;
use Invertus\DibsEasy\Adapter\ToolsAdapter;
use Module;
use OrderState;
use Tab;

/**
 * Class Installer
 *
 * @package Invertus\DibsEasy\Install
 */
class Installer
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @var array
     */
    private $moduleConfiguration;

    /**
     * @var ConfigurationAdapter
     */
    private $configurationAdapter;

    /**
     * @var LanguageAdapter
     */
    private $languageAdapter;

    /**
     * @var TabAdapter
     */
    private $tabAdapter;

    /**
     * @var Db
     */
    private $db;

    /**
     * @var ToolsAdapter
     */
    private $toolsAdapter;

    /**
     * Installer constructor.
     *
     * @param Module $module
     * @param ConfigurationAdapter $configurationAdapter
     * @param LanguageAdapter $languageAdapter
     * @param TabAdapter $tabAdapter
     * @param ToolsAdapter $toolsAdapter
     * @param Db $db
     * @param array $config
     */
    public function __construct(
        Module $module,
        ConfigurationAdapter $configurationAdapter,
        LanguageAdapter $languageAdapter,
        TabAdapter $tabAdapter,
        ToolsAdapter $toolsAdapter,
        Db $db,
        array $config
    ) {
        $this->module = $module;
        $this->moduleConfiguration = $config;
        $this->configurationAdapter = $configurationAdapter;
        $this->languageAdapter = $languageAdapter;
        $this->tabAdapter = $tabAdapter;
        $this->db = $db;
        $this->toolsAdapter = $toolsAdapter;
    }

    /**
     * Install module
     *
     * @return bool
     */
    public function install()
    {
        if (!$this->registerHooks()) {
            return false;
        }

        if (!$this->installConfiguration()) {
            return false;
        }

        if (!$this->installDefaultAddresses()) {
            return false;
        }

        if (!$this->installOrderStates()) {
            return false;
        }

        if (!$this->installTabs()) {
            return false;
        }

        if (!$this->installDatabase()) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall module
     *
     * @return bool
     */
    public function uninstall()
    {
        if (!$this->uninstallOrderStates()) {
            return false;
        }

        if (!$this->uninstallDefaultAddresses()) {
            return false;
        }

        if (!$this->uninstallConfiguration()) {
            return false;
        }

        if (!$this->uninstallTabs()) {
            return false;
        }

        if (!$this->uninstallDatabase()) {
            return false;
        }

        return true;
    }

    /**
     * Register module hooks
     *
     * @return bool
     */
    protected function registerHooks()
    {
        $hooks = $this->moduleConfiguration['hooks'];
        foreach ($hooks as $hookName) {
            if (!$this->module->registerHook($hookName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Install module default configuration
     *
     * @retrun bool
     */
    protected function installConfiguration()
    {
        $configuration = $this->moduleConfiguration['configuration'];

        foreach ($configuration as $name => $value) {
            // skip order state & address configuration
            // since those will be saved later
            if (false !== strpos($name, 'ORDER_STATE') || false !== strpos($name, 'ADDRESS_ID')) {
                continue;
            }

            if (!$this->configurationAdapter->set($name, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uninstall module configuration
     *
     * @return bool
     */
    protected function uninstallConfiguration()
    {
        $configurationNames = array_keys($this->moduleConfiguration['configuration']);

        foreach ($configurationNames as $name) {
            if (!$this->configurationAdapter->remove($name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Install order states
     *
     * @return bool
     */
    protected function installOrderStates()
    {
        $orderStates = $this->moduleConfiguration['order_states'];
        $idLangs = $this->languageAdapter->getIDs();

        foreach ($orderStates as $state) {
            $orderState = new OrderState();
            $orderState->color = $state['color'];
            $orderState->paid = $state['paid'];
            $orderState->invoice = $state['invoice'];
            $orderState->module_name = $this->module->name;
            $orderState->unremovable = 0;

            foreach ($idLangs as $idLang) {
                $orderState->name[$idLang] = $state['name'];
            }

            if (!$orderState->save()) {
                return false;
            }

            $this->configurationAdapter->set($state['config'], $orderState->id);
        }

        return true;
    }

    /**
     * Uninstall order states
     *
     * @return bool
     */
    protected function uninstallOrderStates()
    {
        $orderStates = $this->moduleConfiguration['order_states'];

        foreach ($orderStates as $state) {
            $idOrderState = $this->configurationAdapter->get($state['config']);

            if (!$idOrderState) {
                continue;
            }

            $orderState = new OrderState($idOrderState);
            $orderState->deleted = 1;

            if (!$orderState->save()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Install module tabs
     *
     * @return bool
     */
    protected function installTabs()
    {
        $tabs = $this->moduleConfiguration['tabs'];
        $languages = $this->languageAdapter->getIDs();

        foreach ($tabs as $tab) {
            if ($this->tabAdapter->getIdFromClassName($tab['class_name'])) {
                continue;
            }

            $moduleTab = new Tab();

            foreach ($languages as $idLang) {
                $moduleTab->name[$idLang] = $tab['name'];
            }

            $moduleTab->class_name = $tab['class_name'];
            $moduleTab->id_parent  = -1;
            $moduleTab->module     = $this->module->name;

            if (!$moduleTab->save()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Uninstall module tabs
     *
     * @return bool
     */
    protected function uninstallTabs()
    {
        $tabs = $this->moduleConfiguration['tabs'];

        if (empty($tabs)) {
            return true;
        }

        foreach ($tabs as $tab) {
            $tabId = (int) $this->tabAdapter->getIdFromClassName($tab['class_name']);

            if (!$tabId) {
                continue;
            }

            $tab = new Tab($tabId);

            if (!$tab->delete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Install database tables
     *
     * @retrun bool
     */
    protected function installDatabase()
    {
        $sqlStatements = $this->getSqlStatements($this->module->getLocalPath().'sql/install.sql');

        return $this->execute($sqlStatements);
    }

    /**
     * Uninstall database
     *
     * @return bool
     */
    protected function uninstallDatabase()
    {
        $sqlStatements = $this->getSqlStatements($this->module->getLocalPath().'sql/uninstall.sql');

        return $this->execute($sqlStatements);
    }

    /**
     * Execute SQL statements
     *
     * @param string $sqlStatements
     *
     * @return bool
     */
    protected function execute($sqlStatements)
    {
        try {
            $result = $this->db->execute($sqlStatements);
        } catch (Exception $e) {
            return false;
        }

        return (bool) $result;
    }

    /**
     * Format file sql statements
     *
     * @param string $fileName
     *
     * @return string
     */
    protected function getSqlStatements($fileName)
    {
        $sqlStatements = $this->toolsAdapter->fileGetContents($fileName);
        $sqlStatements = str_replace('PREFIX_', _DB_PREFIX_, $sqlStatements);
        $sqlStatements = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sqlStatements);

        return $sqlStatements;
    }

    /**
     * Install default delivery addresses for supported countries
     */
    protected function installDefaultAddresses()
    {
        if (!$this->installAddress('SE', 'DIBS_SWEEDEN_ADDRESS_ID')) {
            return false;
        }

        if (!$this->installAddress('NO', 'DIBS_NORWAY_ADDRESS_ID')) {
            return false;
        }

        if (!$this->installAddress('DK', 'DIBS_DENMARK_ADDRESS_ID')) {
            return false;
        }

        return true;
    }

    protected function uninstallDefaultAddresses()
    {
        $addressConfigs = array(
            'DIBS_SWEEDEN_ADDRESS_ID',
            'DIBS_NORWAY_ADDRESS_ID',
            'DIBS_DENMARK_ADDRESS_ID',
        );

        foreach ($addressConfigs as $addressConfig) {
            $idAddress = $this->configurationAdapter->get($addressConfig);

            if (!$idAddress) {
                return true;
            }

            $address = new Address($idAddress);
            $address->delete();
        }

        return true;
    }

    private function installAddress($countryIso, $countryAddressConfig)
    {
        $idCountry = Country::getByIso($countryIso);
        $country = new Country($idCountry);

        if (is_array($country->name)) {
            $countryName = reset($country->name);
        } else {
            $countryName = $country->name;
        }

        $address = new Address();
        $address->id_country = $country->id;
        $address->alias = sprintf('Dibs Easy %s', $countryName);
        $address->address1 = 'Address1';
        $address->address2 = '';
        $address->postcode = '00000';
        $address->city = 'Any';
        $address->firstname = 'Dibs';
        $address->lastname = 'Easy';
        $address->phone = '000000000';
        $address->id_customer = 0;
        $address->deleted = 1;

        if (!$address->save()) {
            throw new Exception('Failed to save default address');
        }

        $this->configurationAdapter->set($countryAddressConfig, $address->id);

        return true;
    }
}
