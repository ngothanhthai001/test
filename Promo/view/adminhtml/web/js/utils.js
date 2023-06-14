/**
 * Utils
 */

define([
    'underscore'
], function (_) {
    'use strict';

    return {
        types: ['configurable', 'grouped', 'bundle'],

        /**
         * @param {Object} record
         * @returns {Boolean}
         */
        isConfigurable: function (record) {
            return _.contains(this.types, record['type_id']) || +record['has_options'] === 1;
        }
    };
});
