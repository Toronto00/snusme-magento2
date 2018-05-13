/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/translate',
    'jquery/ui',
], function ($, $t,alert) {
    'use strict';

    return function (widget) {
        $.widget('mage.productValidate', widget, {

            /**
             * Uses Magento's validation widget for the form object.
             * @private
             */
            _create: function () {
                var bindSubmit = this.options.bindSubmit;

                this.element.validation({
                    radioCheckboxClosest: this.options.radioCheckboxClosest,

                    /**
                     * Uses catalogAddToCart widget as submit handler.
                     * @param {Object} form
                     * @returns {Boolean}
                     */
                    submitHandler: function (form, event) {
                        var jqForm = $(form).catalogAddToCart({
                            bindSubmit: bindSubmit
                        });

                        jqForm.catalogAddToCart('submitForm', jqForm);

                        return false;
                    }
                });
            }
        });

        return $.mage.productValidate;
    }
});