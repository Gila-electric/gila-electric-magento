var config = {
    config: {
        mixins: {
            'Amasty_Shopby/js/amShopbyAjax': {
                'Amasty_QuickOrder/js/shopby/amShopbyAjax-mixin': true
            },
            'Amasty_ShopbyLite/js/amShopbyAjax': {
                'Amasty_QuickOrder/js/shopby/amShopbyAjax-mixin': true
            }
        }
    },
    map: {
        "*": {
            "amqorderQty": "Amasty_QuickOrder/js/components/grid/qty",
            "amqorderGrid": "Amasty_QuickOrder/js/components/grid/grid",
            "amqorderMove": "Amasty_QuickOrder/js/components/grid/move",
            "amqorderPriceBox": "Amasty_QuickOrder/js/catalog/price-box"
        }
    }
};
