define([
    'Magento_Ui/js/modal/modal-component',
    'mageUtils',
    'Magento_Ui/js/modal/alert',
    'uiRegistry'
], function (Modal, utils, alert, registry) {
    'use strict';

    return Modal.extend({
        defaults: {
            saveUrl: '',
            modules: {
                listing: '${ $.name }.company_listing'
            },
            customerIds: {}
        },

        initObservable: function () {
            this._super().observe(['customerIds']);
            return this;
        },

        assignCompany: function () {
            var data = {},
                params = _.extend(
                    {},
                    this.params,
                    {
                        ajaxSave: true,
                        ajaxSaveType: 'simple'
                    }
                );
            data.companyId = this.listing().selections().selectedValue();
            data.customerIds = this.customerIds();

            return utils.ajaxSubmit({
                url: this.saveUrl,
                data: data
            }, params).done(function (data) {
                if (typeof data.error === 'undefined') {
                    this.reloadMainGrid();
                } else {
                    alert({content: data.error});
                }
            }.bind(this));
        },

        reloadMainGrid: function () {
            var grid = registry.get('customer_listing.customer_listing_data_source');
            if (grid) {
                grid.params.random = Math.random();
                grid.reload();
            }
        },
    });
});

