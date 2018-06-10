define([
    'jquery',
    'uiComponent',
    'uiRegistry'
], function ($, Component, Registry) {
    'use strict';
    
    return Component.extend({
        defaults: {
            inputSelector:       '#search, #mobile_search, .minisearch input[type="text"]',
            categorySelector:    '[name=cat]',
            placeholderSelector: '.searchautocomplete__autocomplete',
            resizeInterval:      null,
            activeInput:         null,
            activeCategory:      null
        },
        
        initialize: function () {
            this._super();
            
            $(this.inputSelector).on('focus', function (event) {
                this.initializePlaceholder($(event.target));
                this.onFocus(event);
            }.bind(this));
        },
        
        initializePlaceholder: function ($input) {
            var positionInterval, $category;

            if (this.activeInput) {
                this.activeInput
                    .off('focus', this.onFocus)
                    .off('change keydown keyup paste', this.onChange)
                    .off('keydown', this.onKey)
                    .off('blur', this.onBlur);
            }
            
            if (this.activeCategory) {
                this.activeCategory
                    .off('change', this.onChangeCategory);
            }
            
            if (this.resizeInterval) {
                clearInterval(this.resizeInterval);
            }
            
            positionInterval = setInterval($.proxy(function () {
                if ($(this.placeholderSelector).length) {
                    $input.parent().css('position', 'relative');
                    $(this.placeholderSelector).appendTo($input.parent());
                    clearInterval(positionInterval);
                }
            }, this), 100);
            
            this.resizeInterval = setInterval($.proxy(function () {
                var position = $input.position();
                var left = position.left + parseInt($input.css('marginLeft'), 10);
                var top = position.top + parseInt($input.css('marginTop'), 10);
                
                $(this.placeholderSelector)
                    .css('top', $input.outerHeight() - 1 + top)
                    .css('left', left)
                    .css('width', $input.outerWidth());
            }, this), 100);
            
            // remove magento observer
            $input.off('keydown');
            
            $input.on('keyup keydown change paste', this.onChange);
            
            $input.on('keydown', this.onKey);
            
            $input.on('focus', this.onFocus);
            
            $input.on('blur', this.onBlur);
            
            $input.closest('form').on('submit', this.onSubmit);
            
            this.activeInput = $input;
            
            $category = $(this.categorySelector, $input.closest('form'));
            if ($category.length) {
                $category.on('change', this.onChangeCategory);
                this.activeCategory = $category;
            }
            
            Registry.get('autocomplete', function (autocomplete) {
                autocomplete.injection = this;
            }.bind(this));
        },
        
        onFocus: function (event) {
            $('body').addClass('searchautocomplete__active');
            
            Registry.get('autocomplete', function (autocomplete) {
                autocomplete.query($(event.target).val());
                autocomplete._hasFocus(true);
            });
        },
        
        onBlur: function () {
            Registry.get('autocomplete', function (autocomplete) {
                autocomplete._hasFocus(false);
            });
            
            window.setTimeout(function () {
                $('body').removeClass('searchautocomplete__active');
            }, 200);
        },
        
        onChange: function (event) {
            //short delay for all events (specially for paste)
            setTimeout(function () {
                Registry.get('autocomplete', function (autocomplete) {
                    autocomplete.query($(event.target).val());
                });
            }, 50);
        },
        
        onChangeCategory: function (event) {
            Registry.get('autocomplete', function (autocomplete) {
                autocomplete.category($(event.target).val());
            });
        },
        
        onKey: function (event) {
            Registry.get('autocomplete', function (autocomplete) {
                autocomplete.onKey(event.keyCode, event);
                autocomplete.query($(event.target).val());
            });
        },
        
        onSubmit: function () {
            Registry.get('autocomplete', function (autocomplete) {
                autocomplete.onSubmit();
            });
        }
    });
});