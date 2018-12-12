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

{if isset($dibsCheckout)}
<script>
    var dibsCheckout = {
        checkoutKey: "{$dibsCheckout.checkoutKey|escape:'htmlall':'UTF-8'}",
        language: "{$dibsCheckout.language|escape:'htmlall':'UTF-8'}",
        validationUrl: "{$dibsCheckout.validationUrl|escape:'htmlall':'UTF-8'}",
        checkoutUrl: "{$dibsCheckout.checkoutUrl|escape:'htmlall':'UTF-8'}",
        refreshUrl: "{$dibsCheckout.refreshUrl|escape:'htmlall':'UTF-8'}",
        addressUrl: "{$dibsCheckout.addressUrl|escape:'htmlall':'UTF-8'}",
        actions: {
            changeDeliveryOption: "{$dibsCheckout.actions.changeDeliveryOption|escape:'htmlall':'UTF-8'}",
            addDiscount: "{$dibsCheckout.actions.addDiscount|escape:'htmlall':'UTF-8'}"
        },
        paymentID: "{$dibsCheckout.paymentID|escape:'htmlall':'UTF-8'}"
    };
</script>
{/if}
