define([
    'prototype',
    'underscore'
], function ($, _) {
    'use strict';

    return function () {
        window.AdminOrder.prototype.loadArea =
            window.AdminOrder.prototype.loadArea.wrap(function (proceed, area, indicator, params) {
                var areasToUpdateItems = ['shipping_method', 'billing_method', 'card_validation'];

                if ((_.isString(area) && areasToUpdateItems.indexOf(area) !== -1)
                    || (_.isArray(area) && area.intersect(areasToUpdateItems).length)
                ) {
                    area.push('items');
                }

                return proceed(area, indicator, params);
            });
    };
});
