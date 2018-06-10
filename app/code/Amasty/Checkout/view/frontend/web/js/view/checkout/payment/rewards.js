define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Amasty_Checkout/js/action/add-reward',
    'Amasty_Checkout/js/action/cancel-reward'
], function ($, ko, Component, quote, setRewardPointAction, cancelRewardPointAction) {
    'use strict';

    var pointsUsed = ko.observable(null),
        pointsLeft = ko.observable(null),
        isApplied;

    isApplied = ko.observable(pointsUsed() != null);

    return Component.extend({
        defaults: {
            template: 'Amasty_Checkout/checkout/payment/rewards'
        },

        /**
         * Applied flag
         */
        isApplied: isApplied,

        pointsLeft: pointsLeft,

        /**
         *
         * @return {exports}
         */
        initialize: function() {
            this._super();
            pointsUsed(this.pointsUsed);
            pointsLeft(this.pointsLeft - this.pointsUsed);

            if (pointsUsed() > 0) {
                isApplied(true);
            }

            return this;
        },

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function () {
            return this.customerId;
        },

        /**
         * Coupon code application procedure
         */
        apply: function () {
            if (this.validate()) {
                pointsUsed(this.pointsUsed);
                setRewardPointAction(pointsUsed(), isApplied, this.applyUrl);
                this.updatePoints();
            }
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            pointsUsed(0);
            cancelRewardPointAction(isApplied, this.cancelUrl);
            this.updatePoints();
        },

        /**
         *
         * @return {boolean}
         */
        updatePoints: function () {
            pointsLeft(this.pointsLeft - pointsUsed());

            return true;
        },

        /**
         *
         * @return {*}
         */
        getRewardsCount: function () {
            return pointsLeft;
        },

        /**
         *
         * @return {*}
         */
        getPointsRate: function () {
            return this.pointsRate;
        },

        /**
         *
         * @return {*}
         */
        getCurrentCurrency: function () {
            return this.currentCurrencyCode;
        },

        /**
         *
         * @return {*}
         */
        getRateForCurrency: function () {
            return this.rateForCurrency;
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '#discount-reward-form';
            var valueValid = (this.pointsLeft - this.pointsUsed >= 0) && this.pointsUsed > 0;

            return $(form).validation() && $(form).validation('isValid') && valueValid;
        }
    });
});
