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

$(document).ready(function() {
    var $buttonOrderCart = $('#button_order_cart');
    if (0 < $buttonOrderCart.length) {
        $buttonOrderCart.attr('href', dibsGlobal.checkoutUrl);
    }

    var $layerCart = $('#layer_cart');
    if (0 < $layerCart.length) {
        $layerCart.find('a.btn.btn-default.button.button-medium').attr('href', dibsGlobal.checkoutUrl);
    }

    var $shoppingCart = $('#shopping_cart');
    if (0 < $shoppingCart.length) {
        $shoppingCart.find('a').attr('href', dibsGlobal.checkoutUrl);
    }
});
