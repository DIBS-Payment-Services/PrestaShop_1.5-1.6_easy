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

<br>
<fieldset>
    <legend>{l s='DIBS Easy payment actions' mod='dibseasy'}</legend>
    {if $dibsPaymentCanBeCharged}
        <a href="{$dibsChargeUrl|escape:'htmlall':'UTF-8'}"
           class="button js-dibs-confirmation"
           data-confirmation-message="{l s='Are you sure you want to Charge payment?' mod='dibseasy'}"
        >
            {l s='Charge payment' mod='dibseasy'}
        </a>
    {/if}

    {if $dibsPaymentCanBeCanceled}
        <a href="{$dibsCancelUrl|escape:'htmlall':'UTF-8'}"
           class="button js-dibs-confirmation"
           data-confirmation-message="{l s='Are you sure you want to Cancel payment?' mod='dibseasy'}"
        >
            {l s='Cancel payment' mod='dibseasy'}
        </a>
    {/if}

    {if $dibsPaymentCanBeRefunded}
        <a href="{$dibsRefundUrl|escape:'htmlall':'UTF-8'}"
           class="button js-dibs-confirmation"
           data-confirmation-message="{l s='Are you sure you want to Refund payment?' mod='dibseasy'}"
        >
            {l s='Refund payment' mod='dibseasy'}
        </a>
    {/if}
</fieldset>
