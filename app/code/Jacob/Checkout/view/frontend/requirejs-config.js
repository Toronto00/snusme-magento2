var config = {
    map: {
        '*': {
            'Magento_Checkout/template/minicart/item/default.html':
                'Jacob_Checkout/template/minicart/item/default.html',
            'Magento_Checkout/template/minicart/content.html':
                'Jacob_Checkout/template/minicart/content.html',
            'Magento_Checkout/js/model/quote':
                'Jacob_Checkout/js/model/quote'
        }
    },
    config: {
    	mixins: {
    		'Magento_Checkout/js/model/place-order': {
                'Jacob_Checkout/js/model/place-order-mixin': true
            },
    	}
    }
};