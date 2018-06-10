define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiComponent',
    'mage/translate',
    'ko'
], function ($, _, utils, Component, $t, ko) {
    'use strict';
    
    return Component.extend({
        defaults: {
            listens: {
                '${ $.autocomplete }:hasFocus': 'focusHasChanged',
                '${ $.autocomplete }:query':    'queryHasChanged'
            },
            
            exports: {
                result: '${ $.provider }:data',
                query:  '${ $.autocomplete }:query'
            },
            
            result: [],
            
            limit: 30
        },
        
        initialize: function () {
            this._super();
        },
        
        initObservable: function () {
            this._super()
                .observe('result');
            
            this.query = ko.observable('');
            
            return this;
        },
        
        focusHasChanged: function (focus) {
            if (focus && this.query().length === 0) {
                this._showQueries();
            }
        },
        
        queryHasChanged: function (query) {
            this.query(query);
            
            if (this.query().length === 0) {
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
            var item;
            var result, index;
            
            _.each(queries, function (query, idx) {
                if (idx < this.limit) {
                    item = {};
                    item.query = query;
                    item.enter = function () {
                        self.query(query);
                    };
                    
                    items.push(item);
                }
            }, this);
            
            result = {
                totalItems: items.length,
                query:      this.query(),
                indices:    [],
                isShowAll:  false
            };
            
            index = {
                totalItems:   items.length,
                isShowTotals: false,
                items:        items,
                identifier:   'popular',
                title:        $t('Hot Searches')
            };
            
            result.indices.push(index);
            
            this.result(result);
        },
        
        _getQueries: function () {
            return this.queries;
        }
    });
});