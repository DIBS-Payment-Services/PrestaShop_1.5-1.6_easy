{**
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
*}

<a href="{$href|escape:'html':'UTF-8'}"
   title="{$action|escape:'html':'UTF-8'}"
   class="js-dibs-confirmation"
   data-confirmation-message="{l s='Are you sure you want to %s?' mod='dibs' sprintf=[$action]}"
>
    {$action|escape:'html':'UTF-8'}
</a>