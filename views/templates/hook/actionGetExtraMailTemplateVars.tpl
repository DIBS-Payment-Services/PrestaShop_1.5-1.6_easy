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

<br> <br>
<span style="color:#333"><strong>{l s='Order status' mod='dibseasy'}:</strong> {$dibs_order_state|escape:'htmlall':'UTF-8'}</span> <br> <br>
<span style="color:#333"><strong>{l s='Payment ID' mod='dibseasy'}:</strong> {$dibs_payment_id|escape:'htmlall':'UTF-8'}</span> <br> <br>
<span style="color:#333"><strong>{l s='Payment type' mod='dibseasy'}:</strong> {$dibs_payment_type|escape:'htmlall':'UTF-8'}</span> <br> <br>
{if not empty($dibs_masked_pan)}
<span style="color:#333"><strong>{l s='Credit card number' mod='dibseasy'}:</strong> {$dibs_masked_pan|escape:'htmlall':'UTF-8'}</span> <br> <br>
{/if}
<span style="color:#333"><strong>{l s='Delay' mod='dibseasy'}:</strong> {$dibs_delay|escape:'htmlall':'UTF-8'}</span> <br> <br>
<span style="color:#333"><strong>{l s='Contact email' mod='dibseasy'}:</strong> {$dibs_contact_email|escape:'htmlall':'UTF-8'}</span> <br> <br>
