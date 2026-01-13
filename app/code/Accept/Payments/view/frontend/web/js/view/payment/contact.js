define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'contact',
                component: 'Accept_Payments/js/view/payment/method-renderer/contact-method'
            }
        );
        return Component.extend({});
    }
);