define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiComponent',
    'ko',
    'Magento_Catalog/js/catalog-add-to-cart'
], function ($, _, utils, Component, ko) {
    'use strict';
    
    $.ProviderItem = function () {
        this.load = function (obj) {
            this.active = ko.observable(false);
            
            _.each(obj, function (value, key) {
                this[key] = value;
            }, this);
            
            return this;
        };
        
        this.onMouseOver = function () {
            this.active(true);
        };
        
        this.onMouseOut = function () {
            this.active(false);
        };
        
        this.afterRender = function (el) {
            $(el).catalogAddToCart({});
        };
        
        this.formKey = function () {
            var key;
            
            key = _.map($('[name=form_key]'), function (el) {
                return $(el).val();
            });
            key = _.first(_.reject(key, _.isEmpty));
            
            return key;
        };
        
        this.onClick = function (item, event) {
            if (event.button === 0) { // left click
                event.preventDefault();
                
                if (event.target.nodeName === 'A'
                    || event.target.nodeName === 'LI'
                    || event.target.nodeName === 'DIV') {
                    this.enter();
                }
            }
        };
        
        this.enter = function () {
            if (this.url) {
                window.location.href = this.url;
            }
        };
    };
    
    return Component.extend({
        defaults: {
            localStorage: $.initNamespaceStorage('autocomplete-provider').localStorage,
            
            listens: {
                params: 'load',
                data:   'processData'
            },
            
            loading:         false,
            delay:           500,
            minSearchLength: 3,
            mobileWidth:     768,
            params:          {}
        },
        
        initialize: function () {
            this._super();
            
            utils.limit(this, 'load', this.delay);
            
            _.bindAll(this, 'onSuccess');
        },
        
        initConfig: function () {
            this._super();
            
            _.extend(this.data, {
                indices:    [],
                totalItems: 0
            });
            
            return this;
        },
        
        initObservable: function () {
            this._super()
                .observe('loading');
            
            return this;
        },
        
        load: function () {
            var self = this;
            var stored = this.localStorage.get(this._hash(this.get('params')));
            
            if (stored && this.get('params').q.length > this.minSearchLength) {
                // we display cached results, but still load fresh data
                this.onSuccess(stored, this.get('params'));
            }
            
            if (this.get('params').q.length > 0 && this.get('params').q.length < this.minSearchLength) {
                this.onSuccess({
                    query:      this.get('params').q,
                    indices:    [],
                    totalItems: 0
                }, this.get('params'));
                
                return;
            }
            
            if (this.get('params').q.length === 0) {
                return;
            }
            
            if (this.xhr) {
                this.xhr.abort();
            }
            
            this.xhr = $.ajax({
                url:        this.url,
                method:     'GET',
                data:       this.get('params'),
                params:     this.get('params'),
                dataType:   'json',
                beforeSend: function () {
                    if (!stored) {
                        self.loading(true);
                    }
                },
                success:    function (response) {
                    self.onSuccess(response, this.params);
                    self.loading(false);
                }
            });
            
            $(document).ajaxStop(function () {
                if (!stored) {
                    self.loading(false);
                }
            });
        },
        
        processData: function (data) {
            var optimize = false;
            
            if (data.isShowAll === undefined) {
                data.isShowAll = true;
            }
            
            if (data.query !== undefined) {
                data.query = $.trim(data.query.toLowerCase());
                
                
                if (data.optimize !== undefined && data.optimize) {
                    if ($('body').width() < this.mobileWidth) {
                        optimize = true;
                    }
                }
                
                _.each(data.indices, function (index) {
                    _.each(index.items, function (item, key) {
                        item.optimize = optimize;
                        index.items[key] = new $.ProviderItem().load(item);
                    });
                });
                
                this.set('result', data);
            }
        },
        
        onError: function () {
        },
        
        onSuccess: function (data, params) {
            var hash = this._hash(params);
            
            this.localStorage.remove(hash);
            this.localStorage.set(hash, data);
            
            this.processData(data);
        },
        
        _hash: function (object) {
            var string = JSON.stringify(object) + "";
            var hash = 0, i, chr, len;
            
            if (string.length === 0) {
                return hash;
            }
            for (i = 0, len = string.length; i < len; i++) {
                chr = string.charCodeAt(i);
                hash = (hash << 5) - hash + chr;
                hash |= 0;
            }
            return 'h' + hash;
        }
    });
});
