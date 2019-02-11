/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/validation',
    'Magento_Customer/js/customer-data'
], function ($, Component, ko, customer, quote, checkoutData, fullScreenLoader, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Jacob_Checkout/countryinfo',
        },
        model: {
            'info': window.countryInfo,
            'country': ko.observable('')
        },

        initialize: function () {
            this._super();
            var self = this;
            this.model.country(this.getActiveCountry());

            $(document).on('change', "[name='country_id']", function () {
                if (self.model.country() !== self.getActiveCountry()) {
                    self.setCountry(self.getActiveCountry());
                }
            });
        },

        countryHasInfo: function () {
            var country = this.getActiveCountry();

            return (typeof this.model.info[country] !== "undefined");
        },

        getInfo: function () {
            var country = this.getActiveCountry();

            if (typeof this.model.info[country] !== undefined) {
                return this.model.info[country];
            }

            return "";
        },

        getActiveCountry: function () {
            if (!checkoutData.getShippingAddressFromData()) {
                return '';
            }

            return checkoutData.getShippingAddressFromData().country_id;
        },

        setCountry: function (country) {
            this.model.country(country);
        }
    });
});
