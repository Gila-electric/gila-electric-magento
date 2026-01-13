var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/summary/shipping': {
                'Ignitix_ShippingDiscounts/js/view/summary/shipping-mixin': true
            },
            'Magento_Tax/js/view/checkout/summary/shipping': {
                'Ignitix_ShippingDiscounts/js/view/summary/shipping-mixin': true
            }
        }
    }
};