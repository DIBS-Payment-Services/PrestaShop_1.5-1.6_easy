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

<div class="panel">
    <div class="panel-heading">
        <i class="icon-credit-card"></i>
        {l s='DIBS Easy payment actions' mod='dibs'}
    </div>
    <div class="panel-content">
        {if $dibsPaymentCanBeCharged}
            <a href="{$dibsChargeUrl|escape:'htmlall':'UTF-8'}"
               class="btn btn-default js-dibs-confirmation"
               data-confirmation-message="{l s='Are you sure you want to Charge payment?' mod='dibs'}"
            >
                {l s='Charge payment' mod='dibs'}
            </a>
        {/if}

        {if $dibsPaymentCanBeCanceled}
            <a href="{$dibsCancelUrl|escape:'htmlall':'UTF-8'}"
               class="btn btn-default js-dibs-confirmation"
               data-confirmation-message="{l s='Are you sure you want to Cancel payment?' mod='dibs'}"
            >
                {l s='Cancel payment' mod='dibs'}
            </a>
        {/if}

        {if $dibsPaymentCanBeRefunded}
            <a href="{$dibsRefundUrl|escape:'htmlall':'UTF-8'}"
               class="btn btn-default js-dibs-confirmation"
               data-confirmation-message="{l s='Are you sure you want to Refund payment?' mod='dibs'}"
            >
                {l s='Refund payment' mod='dibs'}
            </a>
        {/if}
    </div>
</div>
