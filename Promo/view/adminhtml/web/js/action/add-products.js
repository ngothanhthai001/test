/**
 * Add product action
 */

define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';

    return function (serviceUrl, data) {
        var payload = {
            data: data
        };

        $('body').trigger('processStart');

        $.ajax({
            url: serviceUrl,
            data: payload,
            dataType: 'json',
            method: 'POST'
        }).done(function (response) {
            if (response.success) {
                order.itemsUpdate();
            } else {
                alert({ content: response.error });
            }
        }).fail(function (response) {
            alert({ content: response.error });
        }).always(function () {
            $('body').trigger('processStop');
            window.saveInProgress = false;
        });
    };
});
