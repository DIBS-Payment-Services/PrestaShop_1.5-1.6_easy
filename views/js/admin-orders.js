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
    $('#content').find('.js-dibs-confirmation').on('click', function(event) {
        var message = $(this).data('confirmation-message');
        var confirmed = confirm(message);

        if (confirmed) {
            return true;
        }

        event.stopPropagation();
        event.preventDefault();
    });
});
