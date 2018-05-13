define([
    'jquery',
    'mage/translate',
    'jquery/ui',
], function ($, $t,alert) {
    'use strict';

    return function (widget) {
        $.widget('mage.catalogAddToCart', widget, {

            /**
             * Handler for the form 'submit' event
             *
             * @param {Object} form
             */
            submitForm: function (form) {
                var optionValue = form.find('.product-buy-options-input').first().val(),
                    addToCartButton = form.find('button[data-option-value="' + optionValue + '"]'),
                    self = this;

                if (form.has('input[type="file"]').length && form.find('input[type="file"]').val() !== '') {
                    self.element.off('submit');
                    // disable 'Add to Cart' button
                    addToCartButton = $(form).find(this.options.addToCartButtonSelector);
                    addToCartButton.prop('disabled', true);
                    addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
                    form.submit();
                } else {
                    self.ajaxSubmit(form);
                }
            },

            /**
             * @param {String} form
             */
            disableAddToCartButton: function (form) {
                var addToCartButtonTextWhileAdding = this.options.addToCartButtonTextWhileAdding || $t('Adding...'),
                    addToCartButton = $(document.activeElement);

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
                    addToCartButton = $(document.activeElement),
                    originalTitle = addToCartButton.find('.original-title').html();

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