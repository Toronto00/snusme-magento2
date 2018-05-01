define([
    'jquery',
    'mage/translate',
    'jquery/ui',
], function ($, $t,alert) {
    'use strict';

    return function (widget) {
        $.widget('mage.catalogAddToCart', widget, {

            /**
             * @param {String} form
             */
            disableAddToCartButton: function (form) {
                var addToCartButtonTextWhileAdding = this.options.addToCartButtonTextWhileAdding || $t('Adding...'),
                    addToCartButton = $(form).find(this.options.addToCartButtonSelector);

                addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
                addToCartButton.find('.display').text(addToCartButtonTextWhileAdding);
                addToCartButton.attr('title', addToCartButtonTextWhileAdding);
            },

            /**
            * @param {String} form
            */
            enableAddToCartButton: function (form) {
                var addToCartButtonTextAdded = this.options.addToCartButtonTextAdded || $t('Added'),
                    self = this,
                    addToCartButton = $(form).find(this.options.addToCartButtonSelector),
                    originalTitle = $(form).find('.original-title').html();

                console.log(originalTitle);

                addToCartButton.find('.display').text(addToCartButtonTextAdded);
                addToCartButton.attr('title', addToCartButtonTextAdded);

                setTimeout(function () {
                    var addToCartButtonTextDefault = originalTitle || $t('Buy');

                    addToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
                    addToCartButton.find('.display').html(addToCartButtonTextDefault);
                    addToCartButton.attr('title', addToCartButtonTextDefault);
                }, 1000);
            }
        });

        return $.mage.catalogAddToCart;
    }
});