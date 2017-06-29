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

<div class="row">
    <div class="col-xs-12">
        {include file="$tpl_dir/shopping-cart.tpl"}
    </div>
</div>

{if isset($dibsError)}
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-danger">
            <p>{$dibsError|escape:'htmlall':'UTF-8'}</p>
        </div>
    </div>
</div>
{/if}

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-5">
        {include file="$tpl_dir/order-carrier.tpl"}
    </div>

    <div class="col-xs-12 col-sm-12 col-md-7">
        <h1 class="page-heading">{l s='Easy Checkout' mod='dibs'}</h1>
        <div id="dibs-complete-checkout"></div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <a class="btn btn-default button button-small" href="{$regularCheckoutUrl|escape:'htmlall':'UTF-8'}">
            <span>
                <i class="icon-chevron-left left"></i>
                {l s='Switch to regular checkout' mod='dibs'}
            </span>
        </a>
    </div>
</div>
