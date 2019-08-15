define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Infomodus_Upsap/js/model/shipping-rates-validator',
        'Infomodus_Upsap/js/model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        shippingRatesValidator,
        shippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('upsap', shippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('upsap', shippingRatesValidationRules);
        return Component;
    }
);