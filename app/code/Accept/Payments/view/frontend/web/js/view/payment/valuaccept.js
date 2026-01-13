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
                type: 'valuaccept',
                component: 'Accept_Payments/js/view/payment/method-renderer/valuaccept-method'
            }
        );
        return Component.extend({});
    }
);