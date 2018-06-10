define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiComponent',
    'mage/translate'
], function ($, _, utils, Component, $t) {
    'use strict';
    
    return Component.extend({
        defaults: {
            localStorage: $.initNamespaceStorage('autocomplete-recent').localStorage,
            
            listens: {
                '${ $.autocomplete }:hasFocus':    'focusHasChanged',
                '${ $.autocomplete }:isSubmitted': 'formHasSubmitted'
            },
            
            imports: {
                query: '${ $.autocomplete }:query'
            },
            
            exports: {
                result: '${ $.provider }:data',
                query:  '${ $.autocomplete }:query'
            },
            
            result: [],
            
            limit: 3
        },
        
        initialize: function () {
            this._super();
        },
        
        initObservable: function () {
            this._super()
                .observe('result')
                .observe('query');
            
            return this;
        },
        
        focusHasChanged: function (focus) {
            if (focus && this.query().length === 0) {
                this._showQueries();
            }
        },
        
        formHasSubmitted: function () {
            this._saveQuery();
        },
        
        _showQueries: function () {
            var self = this;
            var queries = this._getQueries();
            var items = [];
            var result, index;
            
            _.each(queries, function (item, idx) {
                if (idx < this.limit) {
                    item.enter = function () {
                        self.query(item.query);
                    };
                    
                    items.push(item);
                }
            }, this);
            
            result = {
                totalItems: items.length,
                query:      '',
                indices:    [],
                isShowAll:  false
            };
            
            index = {
                totalItems:   items.length,
                isShowTotals: false,
                items:        items,
                identifier:   'recent',
                title:        $t('recent searches')
            };
            result.indices.push(index);
            
            this.result(result);
        },
        
        _saveQuery: function () {
            var queries = this._getQueries();
            
            queries.unshift({query: this.query()});
            
            this.localStorage.set('queries', queries);
        },
        
        _getQueries: function () {
            if (this.localStorage.isEmpty('queries')) {
                this.localStorage.set('queries', []);
            }
            
            return this.localStorage.get('queries');
        }
    });
});