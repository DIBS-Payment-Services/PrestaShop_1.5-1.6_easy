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

$(document).ready(function () {
    initCheckout();
    initDeliveryOptionChangeEvent();
    initAddDiscountEvent();
    initUpdateQuantitiesEvent();

    /**
     * Initialize product quantities update in cart
     */
    function initUpdateQuantitiesEvent()
    {
        var $cartSummaryBlock = $('#cart_summary');
        if (0 == $cartSummaryBlock.length) {
            return;
        }

        $cartSummaryBlock.find('a.cart_quantity_delete').unbind('click').on('click', function (event) {
            event.preventDefault();

            // This function is copy/paste from prestashop core with some small modifications
            deleteProductFromSummary($(this).attr('id'));

            return false;
        });

        $cartSummaryBlock.find('a.cart_quantity_up').on('click', function (event) {
            event.preventDefault();

            // This function is copy/paste from prestashop core with some small modifications
            upQuantity($(this).attr('id').replace('cart_quantity_up_', ''));

            return false;
        });

        $cartSummaryBlock.find('a.cart_quantity_down').unbind('click').on('click', function (event) {
            event.preventDefault();

            // This function is copy/paste from prestashop core with some small modifications
            downQuantity($(this).attr('id').replace('cart_quantity_down_', ''));

            return false;
        });
    }

    /**
     * Initialize add discount event
     */
    function initAddDiscountEvent()
    {
        var $discountForm = $('#voucher');
        if (0 == $discountForm.length) {
            return;
        }

        $discountForm.attr('action', dibsCheckout.checkoutUrl);

        $discountForm.on('submit', function () {
            var $actionInput =  $('<input />')
                .attr('type', 'hidden')
                .attr('name', 'action')
                .attr('value', dibsCheckout.actions.addDiscount);

            $discountForm.append($actionInput);
        });
    }

    /**
     * Initialize delivery option change handler
     */
    function initDeliveryOptionChangeEvent()
    {
        var $deliveryOptionForm = $('#carrier_area').find('form');

        // If for some reason form could not be found
        if (0 == $deliveryOptionForm.length) {
            return;
        }

        // Change submit url of delivery option form
        $deliveryOptionForm.attr('action', dibsCheckout.checkoutUrl);
        $deliveryOptionForm.removeAttr('onsubmit');

        // Listen for delivery option change
        $deliveryOptionForm.find('.delivery_option_radio').removeAttr('onchange').on('change', function() {
            var $actionInput =  $('<input />')
                .attr('type', 'hidden')
                .attr('name', 'action')
                .attr('value', dibsCheckout.actions.changeDeliveryOption);

            $deliveryOptionForm.append($actionInput);
            $deliveryOptionForm.submit();
        });
    }

    /**
     * Initialize DIBS checkout iframe
     */
    function initCheckout()
    {
        var checkoutOptions = {
            checkoutKey: dibsCheckout.checkoutKey,
            paymentId : dibsCheckout.paymentID,
            containerId : "dibs-complete-checkout",
            language: dibsCheckout.language
        };

        var checkout = new Dibs.Checkout(checkoutOptions);

        checkout.on('payment-completed', function () {
            window.location = dibsCheckout.validationUrl;
        });

        checkout.on('address-changed', function (address) {
            checkout.freezeCheckout();
            //console.log(address);return;

            $.post(dibsCheckout.addressUrl, {
                post_code: address.postalCode,
                country_code: address.countryCode
            }, function (response) {
                var res = JSON.parse(response);

                if (res.success && res.need_reload) {
                    window.location = dibsCheckout.refreshUrl;
                    return;
                }

                checkout.thawCheckout();
            });
        });
    }

    //  -----------------------------------------------------------------------------------------
    // | Below functions are copy/paste from PrestaShop cart-summary.js with small modifications |
    //  -----------------------------------------------------------------------------------------

    function deleteProductFromSummary(id)
    {
        var customizationId = 0;
        var productId = 0;
        var productAttributeId = 0;
        var id_address_delivery = 0;
        var ids = 0;
        ids = id.split('_');
        productId = parseInt(ids[0]);
        if (typeof(ids[1]) !== 'undefined')
            productAttributeId = parseInt(ids[1]);
        if (typeof(ids[2]) !== 'undefined' && ids[2] !== 'nocustom')
            customizationId = parseInt(ids[2]);
        if (typeof(ids[3]) !== 'undefined')
            id_address_delivery = parseInt(ids[3]);
        $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: baseUri + '?rand=' + new Date().getTime(),
            async: true,
            cache: false,
            dataType: 'json',
            data: 'controller=cart'
            + '&ajax=true&delete=true&summary=true'
            + '&id_product='+productId
            + '&ipa='+productAttributeId
            + '&id_address_delivery='+id_address_delivery
            + ((customizationId !== 0) ? '&id_customization=' + customizationId : '')
            + '&token=' + static_token
            + '&allow_refresh=1',
            success: function(jsonData)
            {
                if (jsonData.hasError)
                {
                    var errors = '';
                    for(var error in jsonData.errors)
                        //IE6 bug fix
                        if(error !== 'indexOf')
                            errors += $('<div />').html(jsonData.errors[error]).text() + "\n";
                    if (!!$.prototype.fancybox)
                        $.fancybox.open([
                                {
                                    type: 'inline',
                                    autoScale: true,
                                    minHeight: 30,
                                    content: '<p class="fancybox-error">' + errors + '</p>'
                                }],
                            {
                                padding: 0
                            });
                    else
                        alert(errors);
                }
                else
                {
                    window.location.reload();
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                if (textStatus !== 'abort')
                {
                    var error = "TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
                    if (!!$.prototype.fancybox)
                        $.fancybox.open([
                                {
                                    type: 'inline',
                                    autoScale: true,
                                    minHeight: 30,
                                    content: '<p class="fancybox-error">' + error + '</p>'
                                }],
                            {
                                padding: 0
                            });
                    else
                        alert(error);
                }
            }
        });
    }

    function upQuantity(id, qty)
    {
        if (typeof(qty) == 'undefined' || !qty)
            qty = 1;
        var customizationId = 0;
        var productId = 0;
        var productAttributeId = 0;
        var id_address_delivery = 0;
        var ids = 0;
        ids = id.split('_');
        productId = parseInt(ids[0]);
        if (typeof(ids[1]) !== 'undefined')
            productAttributeId = parseInt(ids[1]);
        if (typeof(ids[2]) !== 'undefined' && ids[2] !== 'nocustom')
            customizationId = parseInt(ids[2]);
        if (typeof(ids[3]) !== 'undefined')
            id_address_delivery = parseInt(ids[3]);

        $.ajax({
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            url: baseUri + '?rand=' + new Date().getTime(),
            async: true,
            cache: false,
            dataType: 'json',
            data: 'controller=cart'
            + '&ajax=true'
            + '&add=true'
            + '&getproductprice=true'
            + '&summary=true'
            + '&id_product=' + productId
            + '&ipa=' + productAttributeId
            + '&id_address_delivery=' + id_address_delivery
            + ((customizationId !== 0) ? '&id_customization=' + customizationId : '')
            + '&qty=' + qty
            + '&token=' + static_token
            + '&allow_refresh=1',
            success: function(jsonData)
            {
                if (jsonData.hasError)
                {
                    var errors = '';
                    for(var error in jsonData.errors)
                        //IE6 bug fix
                        if(error !== 'indexOf')
                            errors += $('<div />').html(jsonData.errors[error]).text() + "\n";
                    if (!!$.prototype.fancybox)
                        $.fancybox.open([
                                {
                                    type: 'inline',
                                    autoScale: true,
                                    minHeight: 30,
                                    content: '<p class="fancybox-error">' + errors + '</p>'
                                }],
                            {
                                padding: 0
                            });
                    else
                        alert(errors);
                    $('input[name=quantity_'+ id +']').val($('input[name=quantity_'+ id +'_hidden]').val());
                }
                else
                {
                    window.location.reload();
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                if (textStatus !== 'abort')
                {
                    error = "TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
                    if (!!$.prototype.fancybox)
                        $.fancybox.open([
                                {
                                    type: 'inline',
                                    autoScale: true,
                                    minHeight: 30,
                                    content: '<p class="fancybox-error">' + error + '</p>'
                                }],
                            {
                                padding: 0
                            });
                    else
                        alert(error);
                }
            }
        });
    }

    function downQuantity(id, qty)
    {
        var val = $('input[name=quantity_' + id + ']').val();
        var newVal = val;
        if(typeof(qty) == 'undefined' || !qty)
        {
            qty = 1;
            newVal = val - 1;
        }
        else if (qty < 0)
            qty = -qty;

        var customizationId = 0;
        var productId = 0;
        var productAttributeId = 0;
        var id_address_delivery = 0;
        var ids = 0;

        ids = id.split('_');
        productId = parseInt(ids[0]);
        if (typeof(ids[1]) !== 'undefined')
            productAttributeId = parseInt(ids[1]);
        if (typeof(ids[2]) !== 'undefined' && ids[2] !== 'nocustom')
            customizationId = parseInt(ids[2]);
        if (typeof(ids[3]) !== 'undefined')
            id_address_delivery = parseInt(ids[3]);

        if (newVal > 0 || $('#product_' + id + '_gift').length)
        {
            $.ajax({
                type: 'POST',
                headers: { "cache-control": "no-cache" },
                url: baseUri + '?rand=' + new Date().getTime(),
                async: true,
                cache: false,
                dataType: 'json',
                data: 'controller=cart'
                + '&ajax=true'
                + '&add=true'
                + '&getproductprice=true'
                + '&summary=true'
                + '&id_product='+productId
                + '&ipa='+productAttributeId
                + '&id_address_delivery='+id_address_delivery
                + '&op=down'
                + ((customizationId !== 0) ? '&id_customization='+customizationId : '')
                + '&qty='+qty
                + '&token='+static_token
                + '&allow_refresh=1',
                success: function(jsonData)
                {
                    if (jsonData.hasError)
                    {
                        var errors = '';
                        for(var error in jsonData.errors)
                            //IE6 bug fix
                            if(error !== 'indexOf')
                                errors += $('<div />').html(jsonData.errors[error]).text() + "\n";
                        if (!!$.prototype.fancybox)
                            $.fancybox.open([
                                    {
                                        type: 'inline',
                                        autoScale: true,
                                        minHeight: 30,
                                        content: '<p class="fancybox-error">' + errors + '</p>'
                                    }],
                                {
                                    padding: 0
                                });
                        else
                            alert(errors);
                        $('input[name=quantity_' + id + ']').val($('input[name=quantity_' + id + '_hidden]').val());
                    }
                    else
                    {
                        window.location.reload();
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    if (textStatus !== 'abort')
                        alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
                }
            });

        }
        else
        {
            deleteProductFromSummary(id);
        }
    }
});
