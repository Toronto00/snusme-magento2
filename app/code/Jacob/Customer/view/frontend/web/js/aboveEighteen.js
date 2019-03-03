define([
  'jquery',
  'Magento_Ui/js/lib/validation/validator',
  'moment'
], function($, validator, moment){
  'use strict';
  return function() {

    validator.addRule(
      "above-eighteen",
      function (value, maxValue, params) {
        var minValue = moment.utc('1900-01-01', params.dateFormat).unix();
        var givenValue = moment.utc(value, params.dateFormat).unix();

        return givenValue <= maxValue && givenValue >= minValue;
      },
      $.mage.__("You need to be above eighteen years of age.")
    );
   }
});