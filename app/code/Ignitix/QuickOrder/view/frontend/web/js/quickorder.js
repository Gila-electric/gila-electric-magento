require([
    'jquery',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, urlBuilder, customerData) {
	console.log("Quick Order script loaded");

    function normalizeNumber(str) {
        const arabicNums = {'٠':'0','١':'1','٢':'2','٣':'3','٤':'4','٥':'5','٦':'6','٧':'7','٨':'8','٩':'9'};
        return str.replace(/[٠-٩]/g, d => arabicNums[d] ?? d).replace(/[^\d.-]/g, '');
    }

    function renderProductRow(product) {
        const existingRow = $('#quick-order-list .quick-order-row[data-sku="' + product.sku + '"]');
        if (existingRow.length) {
            const qtyInput = existingRow.find('.qty-input');
            qtyInput.val(parseInt(qtyInput.val()) + 1).trigger('change');
            return;
        }

        const rowId = product.sku.replace(/[^a-zA-Z0-9]/g, '') + '_' + new Date().getTime();
        const cleanPrice = parseFloat(normalizeNumber(product.price.toString())) || 0;

        const row = $(`
<div class="quick-order-row" data-id="${rowId}" data-sku="${product.sku}">
    <div class="col-thumbnail"><img src="${product.thumbnail}" alt="${product.name}" style="max-width: 25px;" /></div>
    <div class="col-name" data-label="${$.mage.__('Name:')}">${product.name}</div>
    <div class="col-sku" data-label="${$.mage.__('SKU:')}">${product.sku}</div>
    <div class="col-unit-price unit-price" data-label="${$.mage.__('Unit Price:')}" data-price="${cleanPrice}">${product.price}</div>
    <div class="col-quantity" data-label="${$.mage.__('Quantity:')}"><input type="number" min="1" value="1" class="qty-input" /></div>
    <div class="col-total-price total-price" data-label="${$.mage.__('Total Price:')}">${cleanPrice.toFixed(2)}</div>
    <div class="col-availability" data-label="${$.mage.__('Availability:')}"><span class="status">-</span></div>
    <div class="col-remove"><span class="delete-btn">${$.mage.__('Remove')}</span></div>
</div>`);

        row.find('.qty-input').on('change', function () {
            const qty = parseInt($(this).val());
            const unitPrice = parseFloat(row.find('.unit-price').data('price'));
            row.find('.total-price').text((unitPrice * qty).toFixed(2));
            updateSummary();
            checkAvailability(product.sku, qty, row);
        });

        row.find('.delete-btn').on('click', function () {
            row.remove();
            updateSummary();
        });

        $('#quick-order-list').append(row);
        checkAvailability(product.sku, 1, row);
        updateSummary();
    }

    function updateSummary() {
        let totalItems = 0;
        let totalPrice = 0.0;

        $('#quick-order-list .quick-order-row').each(function () {
            const qty = parseInt($(this).find('.qty-input').val());
            const unitPrice = parseFloat($(this).find('.unit-price').data('price'));
            totalItems += qty;
            totalPrice += qty * unitPrice;
        });

        $('#summary-total-items').text(totalItems);
        $('#summary-total-price').text(totalPrice.toFixed(2));
    }

    function checkAvailability(sku, qty, rowElement) {
        $.ajax({
            url: urlBuilder.build('quickorder/ajax/check'),
            type: 'POST',
            data: { product: sku, qty: qty },
            success: function (response) {
                const statusElem = rowElement.find('.status');
                if (response.success) {
                    statusElem.text('✔').addClass('checkmark').removeClass('error');
                } else {
                    statusElem.text('✖').addClass('error').removeClass('checkmark');
                    alert(response.message);
                }
            }
        });
    }

    function fetchSuggestions(query) {
        console.log("fetchSuggestions called with:", query);
        if (!query) {
            $('#quick-order-suggestions').hide();
            return;
        }

        const storeId = window.storeId || 1;
        console.log("AJAX to suggest:", query, "Store:", storeId);

        $.ajax({
            url: urlBuilder.build('quickorder/ajax/suggest'),
            type: 'GET',
            data: {
                query: query,
                store: storeId
            },
            success: function (products) {
                console.log("Response from suggest:", products);
                const suggestions = $('#quick-order-suggestions');
                suggestions.empty().show();

                products.slice(0, 10).forEach(p => {
                    const item = $(`<li class="suggestion-item" data-sku="${p.sku}" data-thumb="${p.thumbnail}" data-price="${p.price}" data-name="${p.name}">
                        <img src="${p.thumbnail}" alt="${p.name}" style="max-width:25px;"/> ${p.name} (${p.sku}) - ${p.price}
                    </li>`);
                    item.on('click', function () {
                        renderProductRow({
                            sku: p.sku,
                            name: p.name,
                            price: p.price,
                            thumbnail: p.thumbnail
                        });
                        $('#quick-order-search').val('');
                        suggestions.hide();
                    });
                    suggestions.append(item);
                });
            },
            error: function (xhr) {
                console.error("Error in suggest AJAX:", xhr);
            }
        });
    }

    function collectValidItems() {
        const items = [];
        $('#quick-order-list .quick-order-row').each(function () {
            const row = $(this);
            const sku = row.data('sku');
            const qty = parseInt(row.find('.qty-input').val());
            const valid = row.find('.status').hasClass('checkmark');
            if (sku && qty > 0 && valid) {
                items.push({ product: sku, qty });
            }
        });
        return items;
    }

    $(document).ready(function () {
        console.log("Quick Order script loaded");
        const suggestionsBox = $('#quick-order-suggestions');

        $('#quick-order-search').on('keyup', function () {
            fetchSuggestions($(this).val());
        });

        $('#quick-order-search').on('focus', function () {
            if ($('#quick-order-suggestions').children().length) {
                $('#quick-order-suggestions').show();
            }
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#quick-order-search-section').length) {
                suggestionsBox.hide();
            }
        });

        $('#add-to-cart-btn').click(function () {
            const items = collectValidItems();
            if (!items.length) {
                alert($.mage.__('Please enter valid product(s).'));
                return;
            }

            $.ajax({
                url: urlBuilder.build('quickorder/ajax/add'),
                type: 'POST',
                data: { items: JSON.stringify(items) },
                success: function (response) {
                    if (response.success) {
                        alert($.mage.__('Products added to cart.'));
                        customerData.reload(['cart'], true);
                    } else {
                        alert($.mage.__('Failed to add some products:') + ' ' + response.message);
                    }
                }
            });
        });
    });
});