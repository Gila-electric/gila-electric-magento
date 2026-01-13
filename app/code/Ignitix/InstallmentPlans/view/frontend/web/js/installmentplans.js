define([
    'jquery',
    'mage/translate'
], function($, $t) {
    'use strict';

    return function(config) {

        // Global default Terms & Conditions (used if a plan does not define its own)
        var defaultTerms = [
            $t('The Installment Amount Is Approximate.'),
            $t('The Actual Amount Will Be Shown During The Payment Process.'),
            $t('You may be charged purchase fees.'),
            $t('Promo codes are not valid with installment plans.')
        ];

        // 1. Static Data (English) for Banks
        var banksData = [
            {
                name: $t('NBE'),
                logo: 'Ignitix_InstallmentPlans/images/nbe.jpg',
                plans: [
                    { months: 6,  interestRate: 0 },
					{ months: 9,  interestRate: 0.1193 },
					{ months: 10,  interestRate: 0.1298 },
					{ months: 12,  interestRate: 0 },
					{ months: 18,  interestRate: 0.2076 },
					{ months: 24,  interestRate: 0.2598 },
					{ months: 30,  interestRate: 0.3073 },
                    { months: 36, interestRate: 0.3507 }
                ]
            },
            {
                name: $t('Misr'),
                logo: 'Ignitix_InstallmentPlans/images/misr.jpg',
                plans: [
                    { months: 6,  interestRate: 0 },
                    { months: 12,  interestRate: 0 },
                    { months: 18, interestRate: 0.2450 },
					{ months: 24, interestRate: 0.2950 },
					{ months: 36, interestRate: 0.3850 }
                ]
            },
            {
                name: $t('BDC'),
                logo: 'Ignitix_InstallmentPlans/images/bdc.jpg',
                plans: [
                    { months: 6,  interestRate: 0 },
                    { months: 12,  interestRate: 0 },
                    { months: 18, interestRate: 0.2575 },
					{ months: 24, interestRate: 0.34 }
                ]
            },
            {
                name: $t('NBD'),
                logo: 'Ignitix_InstallmentPlans/images/nbd.jpg',
                plans: [
                    { months: 6,  interestRate: 0 },
                    { months: 12,  interestRate: 0 },
                    { months: 18, interestRate: 0.17 },
					{ months: 24, interestRate: 0.2250 }
                ]
            },
            {
			name: $t('Mashreq'),
                logo: 'Ignitix_InstallmentPlans/images/mashreq.jpg',
                plans: [
                    { months: 3,  interestRate: 0.13 },
                    { months: 6,  interestRate: 0 },
                    { months: 9, interestRate: 0.2050 },
					{ months: 12, interestRate: 0 }
                ]
            },
            {
			name: $t('CIB'),
                logo: 'Ignitix_InstallmentPlans/images/cib.jpg',
                plans: [
                    { months: 3,  interestRate: 0.0590 },
                    { months: 6, interestRate: 0 },
					{ months: 10,  interestRate: 0.1150 },
                    { months: 12, interestRate: 0 },
					{ months: 18,  interestRate: 0.19 },
                    { months: 24, interestRate: 0.2450 },
					{ months: 36,  interestRate: 0.3390 }
                ]
            },
			{
                name: $t('NXT'),
                logo: 'Ignitix_InstallmentPlans/images/nxt.jpg',
                plans: [
                    { months: 3,  interestRate: 0.0506 },
                    { months: 6, interestRate: 0 },
                    { months: 12, interestRate: 0 },
					{ months: 18,  interestRate: 0.23 },
                    { months: 24, interestRate: 0.30 }
                ]
            },
			{
                name: $t('NBK'),
                logo: 'Ignitix_InstallmentPlans/images/nbk.jpg',
                plans: [
                    { months: 6,  interestRate: 0 },
                    { months: 12, interestRate: 0 },
					{ months: 18,  interestRate: 0.2350 },
                    { months: 24, interestRate: 0.3050 },
					{ months: 36,  interestRate: 0.4350 }
                ]
            }
        ];

        // 2. Static Data (English) for Microfinance
        var microfinanceData = [
            {
                name: $t('Forsa'),
                logo: 'Ignitix_InstallmentPlans/images/forsa.jpg',
                plans: [
                    { months: 3,  interestRate: 0.10, terms: [$t('Admin Fees: 0%'), $t('The Installment Amount Is Approximate.'), $t('The Actual Amount Will Be Shown During The Payment Process.'), $t('Promo codes are not valid with installment plans.')] },
                    { months: 6,  interestRate: 0.20, terms: [$t('Admin Fees: 0%'), $t('The Installment Amount Is Approximate.'), $t('The Actual Amount Will Be Shown During The Payment Process.'), $t('Promo codes are not valid with installment plans.')] },
                    { months: 9, interestRate: 0.30, terms: [$t('Admin Fees: 0%'), $t('The Installment Amount Is Approximate.'), $t('The Actual Amount Will Be Shown During The Payment Process.'), $t('Promo codes are not valid with installment plans.')] },
		    { months: 12, interestRate: 0.33, terms: [$t('Admin Fees: 0%'), $t('The Installment Amount Is Approximate.'), $t('The Actual Amount Will Be Shown During The Payment Process.'), $t('Promo codes are not valid with installment plans.')] },
		    { months: 18, interestRate: 0.30, terms: [$t('Admin Fees: 0%'), $t('The Installment Amount Is Approximate.'), $t('The Actual Amount Will Be Shown During The Payment Process.'), $t('Promo codes are not valid with installment plans.')] },
	            { months: 24, interestRate: 0.30, terms: [$t('Admin Fees: 0%'), $t('The Installment Amount Is Approximate.'), $t('The Actual Amount Will Be Shown During The Payment Process.'), $t('Promo codes are not valid with installment plans.')] },
		    { months: 30, interestRate: 0.29, terms: [$t('Admin Fees: 0%'), $t('The Installment Amount Is Approximate.'), $t('The Actual Amount Will Be Shown During The Payment Process.'), $t('Promo codes are not valid with installment plans.')] },
		    { months: 36, interestRate: 0.29, terms: [$t('Admin Fees: 0%'), $t('The Installment Amount Is Approximate.'), $t('The Actual Amount Will Be Shown During The Payment Process.'), $t('Promo codes are not valid with installment plans.')] },
		    { months: 48, interestRate: 0.28, terms: [$t('Admin Fees: 0%'), $t('The Installment Amount Is Approximate.'), $t('The Actual Amount Will Be Shown During The Payment Process.'), $t('Promo codes are not valid with installment plans.')] },
                    { months: 61, interestRate: 0.28, terms: [$t('Admin Fees: 0%'), $t('The Installment Amount Is Approximate.'), $t('The Actual Amount Will Be Shown During The Payment Process.'), $t('Promo codes are not valid with installment plans.')] }
                ]
            },
            {
			name: $t('VALU'),
                logo: 'Ignitix_InstallmentPlans/images/valu.jpg',
                plans: [
                    
                ]
            },
			{
                name: $t('Aman'),
                logo: 'Ignitix_InstallmentPlans/images/aman.jpg',
                plans: [
                    
                ]
            },
            {
                name: $t('Souhoola'),
                logo: 'Ignitix_InstallmentPlans/images/souhoola.jpg',
                plans: [
                    
                ]
            }
        ];

        // 3. Function to populate the entities in the sidebar
        function renderEntities(entities, containerId) {
            var container = $('#' + containerId);
            container.empty();

            $.each(entities, function(index, entity) {
                // Convert the logo path to a valid static URL
                var logoUrl = require.toUrl(entity.logo);

                var entityHtml = `
                    <div class="entity" data-entity='${JSON.stringify(entity)}'>
                        <img src="${logoUrl}" alt="${entity.name}" />
                        <span>${entity.name}</span>
                    </div>
                `;
                container.append(entityHtml);
            });
        }

        // 4. On document ready, set up the sidebar behavior and events
        $(document).ready(function() {
            // Slide open sidebar when the button is clicked
            $('#installment-plans-button').on('click', function() {
                $('#installment-plans-sidebar').addClass('open');
            });
            // Slide close sidebar when the close button is clicked
            $('#close-installment-sidebar').on('click', function() {
                $('#installment-plans-sidebar').removeClass('open');
            });

            // Tab switching between banks and microfinance
            $('.installment-tabs li').on('click', function() {
                $('.installment-tabs li').removeClass('active');
                $(this).addClass('active');

                var tabToShow = $(this).data('tab');
                $('.tab-content').removeClass('active');
                $('#' + tabToShow).addClass('active');
            });

            // Render the banks and microfinance entities
            renderEntities(banksData, 'banks-entities');
            renderEntities(microfinanceData, 'microfinance-entities');

            // Set up variable for the product price (from config or default)
            var productPrice = config.productPrice || 600;
            var selectedEntity = null;

            // When a user clicks on an entity, show available plans
            $('.entities-list').on('click', '.entity', function() {
                $(this).siblings().removeClass('active');
                $(this).addClass('active');

                selectedEntity = JSON.parse($(this).attr('data-entity'));

                // Show the plans section and clear previous content
                $('#installment-plans-durations').show().empty();

                $.each(selectedEntity.plans, function(i, plan) {
                    // Include the plan index so we can later access plan-specific terms
                    var planHtml = `
                        <button class="plan-duration" data-index="${i}" data-months="${plan.months}" data-interest="${plan.interestRate}">
                            ${plan.months} ${config.monthsLabel}
                        </button>
                    `;
                    $('#installment-plans-durations').append(planHtml);
                });

                // Clear previous result
                $('#installment-result').empty();
            });

            // When a plan is selected, calculate and display monthly installment along with terms
            $('#installment-plans-durations').on('click', '.plan-duration', function() {
                var index = $(this).data('index'); // get the plan index
                var months = parseInt($(this).data('months'));
                var interestRate = parseFloat($(this).data('interest'));
                var totalPrice = productPrice + (productPrice * interestRate);
                var monthly = totalPrice / months;

                // Retrieve the specific plan's terms if defined; otherwise use defaultTerms
                var selectedPlan = selectedEntity.plans[index];
                var planTerms = selectedPlan.terms ? selectedPlan.terms : defaultTerms;

                // Build the result HTML including monthly installment and terms for this plan
                var resultHtml = `
                    <h3>${monthly.toFixed(2)} EGP / Month</h3>
                    <p>${months} ${config.monthsLabel} Installments</p>
                    <ul>
                `;
                $.each(planTerms, function(i, term) {
                    resultHtml += `<li>${term}</li>`;
                });
                resultHtml += '</ul>';

                $('#installment-result').html(resultHtml);
            });
        });
    };
});
