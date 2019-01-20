define(
    [
        'uiComponent',
        'Magento_Customer/js/customer-data',
        'ko',
        'jquery'
    ],
    function(Component, data, ko, $) {

    /**
     * @param {Object} data
     */
    saveData = function (overlayData) {
        data.set('weightoverlay', overlayData);
    },

    getData = function () {
        var overlayData = data.get('weightoverlay')();

        if ($.isEmptyObject(data)) {
            overlayData = {
                'display_overlay': false,
                'selected_country': null
            };
            saveData(overlayData);
        }

        return overlayData;
    };

    return Component.extend({
        overlay: {
            'display_overlay': ko.observable(false),
            'selected_country': ko.observable()
        },
        shoppingCartUrl: window.checkout.shoppingCartUrl,
        setDeliveryUrl: window.weightoverlay.setDeliveryUrl,
        countryOptions: window.weightoverlay.countryOptions,

        initialize: function () {
            var overlayData = getData();
            this._super();

            this.update(overlayData);

            data.get('weightoverlay').subscribe(function (updatedOverlay) {
                this.update(updatedOverlay);
            }, this);
        },

        /**
         * Update overlay state.
         *
         * @param {Object} updatedOverlay
         * @returns void
         */
        update: function (updatedOverlay) {
            _.each(updatedOverlay, function (value, key) {
                if (!this.overlay.hasOwnProperty(key)) {
                    this.overlay[key] = ko.observable();
                }
                this.overlay[key](value);
            }, this);
        },

        updateCountry: function (_, event) {
            var value = event.target.value;

            this.update(
                {
                    selected_country: value
                }
            );
        },

        /**
         * Get cart param by name.
         * @param {String} name
         * @returns {*}
         */
        getOverlayParam: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.overlay.hasOwnProperty(name)) {
                    this.overlay[name] = ko.observable();
                }
            }

            return this.overlay[name]();
        },

        hideOverlay: function () {
            this.update(
                {
                    display_overlay: false
                }
            );
        },

        getCountries: function () {
            return this.countryOptions;
        },

        changeCountry: function () {
            var self = this;

            $.ajax({
                url: this.setDeliveryUrl,
                data: { selected_country: this.getOverlayParam('selected_country') },
                type: 'post',
                dataType: 'json',

                /** @inheritdoc */
                beforeSend: function () {
                },

                /** @inheritdoc */
                success: function (res) {

                },

                error: function (res) {
                }
            });
        },

        proceedToCheckout: function () {
            window.location = this.shoppingCartUrl;
        }
    });
});