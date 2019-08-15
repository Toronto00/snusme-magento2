define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Infomodus_Caship/js/model/shipping-rates-validator',
        'Infomodus_Caship/js/model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        shippingRatesValidator,
        shippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('caship', shippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('caship', shippingRatesValidationRules);
        return Component;
    }
);