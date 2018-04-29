define([
    'jquery',
    'mage/translate',
    'jquery/ui',
], function ($, $t,alert) {
    'use strict';

    return function (widget) {
        $.widget('mage.catalogAddToCart', widget, {
            originalButtonText: '',

            /**
             * Handler for the form 'submit' event
             *
             * @param {Object} form
             */
            submitForm: function (form) {
                var addToCartButton, self = this;
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);
                console.log(addToCartButton);

                if (form.has('input[type="file"]').length && form.find('input[type="file"]').val() !== '') {
                    self.element.off('submit');
                    // disable 'Add to Cart' button
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
            enableAddToCartButton: function (form) {
                console.log('d');
                var addToCartButtonTextAdded = this.options.addToCartButtonTextAdded || $t('Added'),
                    self = this,
                    addToCartButton = $(form).find(this.options.addToCartButtonSelector);

                addToCartButton.find('span').text(addToCartButtonTextAdded);
                addToCartButton.attr('title', addToCartButtonTextAdded);

                setTimeout(function () {
                    var addToCartButtonTextDefault = self.options.addToCartButtonTextDefault || $t('Add to Cart');

                    addToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
                    addToCartButton.find('span').text(addToCartButtonTextDefault);
                    addToCartButton.attr('title', addToCartButtonTextDefault);
                }, 1000);
            }
        });

        return $.mage.catalogAddToCart;
    }
});