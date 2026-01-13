define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'ko',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/model/messageList'
    ],
    function (
        Component,
        $,
        ko,
        additionalValidators,
        url,
        placeOrderAction,
        fullScreenLoader,
        messageList
    ) {
        return Component.extend({
            defaults: {
                template: 'Accept_Payments/payment/wallet',
                success: false,
                wallet_url: null,
                detail: null,
                phoneNumber:ko.observable('')
            },
            afterPlaceOrder: function (data, event) {
                var self = this;
                fullScreenLoader.startLoader();
                $.ajax({
                    type: 'POST',
                    url: url.build('accept/methods/walletmethod') +"?walletPhone="+ this.phoneNumber(),
                    data: data,
                    success: function (response) {
                        fullScreenLoader.stopLoader();
                        if (response.success) {
                            console.log("afterPlaceOrder:success");
                            console.log(response)
                            self.renderPayment(response);
                        } else {
                            console.log("afterPlaceOrder:error");
                            console.log(response)
                            self.renderErrors(response);
                        }
                    },
                    error: function (response) {
                        console.log("afterPlaceOrder:error");
                        console.log(response)
                        fullScreenLoader.stopLoader();
                        self.renderErrors(response);
                    }
                });
            },
            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                if (this.phoneNumber() && this.phoneNumber().length === 11){
                    if (additionalValidators.validate()) {
                        placeOrder = placeOrderAction(
                            this.getData(),
                            false,
                            this.messageContainer
                        );

                        $.when(placeOrder).done(this.afterPlaceOrder.bind(this));
                        return true;
                    }
                }else {
                    $('#wallet-phone-input').css('border-color','red');
                    $('#wallet-phone-required').show();
                }
                return false;
            },
            renderPayment: function (data) {
                window.location.href = data.wallet_url;
            },
            renderErrors: function (data) {
                fullScreenLoader.stopLoader();
                document.body.scrollTop = 0;
                document.documentElement.scrollTop = 0;
                messageList.addErrorMessage({message:data.message.replace( /(<([^>]+)>)/ig, '')});
                setTimeout(function () {
                    window.location.href = window.location.href.replace('#payment','cart');
                },5);
            },
            getData: function () {
                return {"method": this.item.method};
            },
            logo: function () {
                return window.checkoutConfig.payment[this.getCode()].logo;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment[this.getCode()].instructions;
            }
        });
    }
);
