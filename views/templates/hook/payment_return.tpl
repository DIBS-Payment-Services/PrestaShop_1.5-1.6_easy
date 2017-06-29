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

<p class="alert alert-success">{l s='Your order on is complete.' mod='dibs'}</p>
<p>{l s='Order status' mod='dibs'}: {$currentOrderState|escape:'htmlall':'UTF-8'}</p>
<p class="cart_navigation exclusive">
    <a class="button-exclusive btn btn-default"
       href="{$orderDetailsUrl|escape:'htmlall':'UTF-8'}"
       title="{l s='Go to your order details page' mod='dibs'}"
    >
        <i class="icon-chevron-left"></i>{l s='View order details' mod='dibs'}
    </a>
</p>