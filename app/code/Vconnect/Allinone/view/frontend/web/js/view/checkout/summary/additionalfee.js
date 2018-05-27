define(
    [
        'jquery',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function ($, Component, quote, priceUtils, totals) {
        "use strict";

        var vconnect_allinone_config = window.checkoutConfig.vconnect_allinone;

        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Vconnect_Allinone/checkout/summary/additionalfee'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function() {
                return this.isFullMode();
            },
            getValue: function() {
                var price = 0;
                if (this.totals() && totals.getSegment('additionalfee') !== null) {
                    price = totals.getSegment('additionalfee').value;
                }
                return this.getFormattedPrice(price);
            },
            getBaseValue: function() {
                var price = 0;
                if (this.totals() && totals.getSegment('additionalfee') !== null) {
                    price = totals.getSegment('additionalfee').base_value;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            },
            getIsActive: function() {
                if (this.totals() && totals.getSegment('additionalfee') !== null) {
                    return true;
                } else {
                    return false;
                }
            },
            getAdditionalfeeTitle: function() {
                var additionalfee_title = '';
                if (this.totals() && totals.getSegment('additionalfee') !== null) {
                    $.ajax({
                        url: vconnect_allinone_config.get_additionalfee_title_url,
                        type: 'POST',
                        dataType: 'html',
                        async: false,
                        showLoader: true,
                        success: function(title) {
                            additionalfee_title = title;
                        }
                    });
                }

                return additionalfee_title;
            }
        });
    }
);
