<?php

namespace MagePsycho\RegionCityPro\Plugin\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use MagePsycho\RegionCityPro\Helper\Data as RegionCityProHelper;
use MagePsycho\RegionCityPro\Api\Data\CityInterface;

/**
 * @category   MagePsycho
 * @package    MagePsycho_RegionCityPro
 * @author     Raj KB <magepsycho@gmail.com>
 * @website    https://www.magepsycho.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LayoutProcessorPlugin
{
    const CITY_ID_SORT_ORDER_OLD = 81;
    const CITY_ID_SORT_ORDER_NEW = 91;

    /**
     * @var RegionCityProHelper
     */
    protected $regionCityProHelper;

    /**
     * @var int
     */
    private $cityIdOrder;

    public function __construct(
        RegionCityProHelper $regionCityProHelper
    ) {
        $this->regionCityProHelper = $regionCityProHelper;
    }

    public function afterProcess(
        LayoutProcessor $subject,
        array $jsLayout
    ) {
        if ($this->regionCityProHelper->isFxnSkipped()) {
            return $jsLayout;
        }

        // Add shipping address field
        $cityRenderOptions = $this->prepareCityRenderOptions();
        $jsLayout = $this->addShippingAddressCityIdField($jsLayout, $cityRenderOptions);
        $jsLayout = $this->addShippingAddressCityVisibility($jsLayout);

        // Add billing address field
        $paymentForms = $jsLayout['components']['checkout']['children']['steps']['children']
        ['billing-step']['children']['payment']['children']['payments-list']['children'];

        foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
            $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);
            if (! isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form'])) {
                continue;
            }
            $jsLayout = $this->addBillingAddressCityIdField($jsLayout, $paymentMethodCode, $cityRenderOptions);
            $jsLayout = $this->addBillingAddressCityVisibility($jsLayout, $paymentMethodCode);
        }

        return $jsLayout;
    }

    private function addShippingAddressCityIdField($jsLayout, $renderOptions = [])
    {
        $cityIdField = [
            'component' => 'MagePsycho_RegionCityPro/js/form/element/city',
            
            'config'    => [
                'customScope'   => 'shippingAddress.custom_attributes',
                'customEntry'   => 'shippingAddress.custom_attributes.city_id',
                'template'      => 'ui/form/field',
                'elementTmpl'   => 'ui/form/element/select',
            ],
            'label' => __('City'),
            //'value' => '',
            'dataScope' => 'shippingAddress.custom_attributes.' . CityInterface::ID,
            'provider' => 'checkoutProvider',
            'sortOrder' => $renderOptions['sortOrder'],
            'customEntry' => null,
            'visible' => true,
            'options' => [],
            'validation' => [
                'required-entry' => true,
            ],
            'filterBy'  => [
                'target' => '${ $.provider }:shippingAddress.region_id',
                'field'  => 'region_id'
            ],
            'imports'   => [
                'initialOptions'    => 'index = checkoutProvider:dictionaries.' . CityInterface::ID,
                'setOptions'        => 'index = checkoutProvider:dictionaries.' . CityInterface::ID
            ]
        ];
        if ($renderOptions['searchable']) {
            $cityIdField['config']['elementTmpl'] = 'MagePsycho_RegionCityPro/select2';
        }

        $jsLayout['components']['checkout']['children']['steps']['children']
        ['shipping-step']['children']['shippingAddress']['children']
        ['shipping-address-fieldset']['children'][CityInterface::ID] = $cityIdField;
        return $jsLayout;
    }

    private function addShippingAddressCityVisibility($jsLayout)
    {
        if (isset(
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['city']
        )) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']
            ['children']['city']['visible'] = false;
        }
        return $jsLayout;
    }

    private function addBillingAddressCityIdField($jsLayout, $paymentMethodCode, $renderOptions = [])
    {
        $cityIdField = [
            'component' => 'MagePsycho_RegionCityPro/js/form/element/city',
            'config' => [
                'customScope'   => 'billingAddress' . $paymentMethodCode . '.custom_attributes',
                'customEntry'   => 'billingAddress' . $paymentMethodCode . '.city',
                'template'      => 'ui/form/field',
                'elementTmpl'   => 'ui/form/element/select'
            ],
            'label' => __('City'),
            //'value' => '',
            'dataScope' =>  'billingAddress' . $paymentMethodCode . '.custom_attributes.' . CityInterface::ID,
            'provider' => 'checkoutProvider',
            'sortOrder' => $renderOptions['sortOrder'],
            'customEntry' => null,
            'visible' => true,
            'options' => [],
            'validation' => [
                'required-entry' => true,
            ],
            'filterBy' => [
                'target'    => '${ $.provider }:billingAddress' . $paymentMethodCode . '.region_id',
                'field'     => 'region_id'
            ],
            'imports'   => [
                'initialOptions'    => 'index = checkoutProvider:dictionaries' . '.' . CityInterface::ID,
                'setOptions'        => 'index = checkoutProvider:dictionaries' . '.' . CityInterface::ID
            ]
        ];
        if ($renderOptions['searchable']) {
            $cityIdField['config']['elementTmpl'] = 'MagePsycho_RegionCityPro/select2';
        }

        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']['children']
        ['form-fields']['children'][CityInterface::ID] = $cityIdField;
        return $jsLayout;
    }

    private function addBillingAddressCityVisibility($jsLayout, $paymentMethodCode)
    {
        if (isset(
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']
            ['children']['form-fields']['children']['city']
        )) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']
            ['children']['form-fields']['children']['city']['visible'] = false;
        }
        return $jsLayout;
    }

    private function prepareCityRenderOptions()
    {
        return [
            'sortOrder' => $this->getCityIdSortOrder(),
            'searchable' => $this->regionCityProHelper->getConfigHelper()->isCitySearchable()
        ];
    }

    private function getCityIdSortOrder()
    {
        if ($this->cityIdOrder) {
            return $this->cityIdOrder;
        }

        $sortOrder = $this->regionCityProHelper->getConfigHelper()->getCitySortOrder();
        if (!$sortOrder) {
            $sortOrder = version_compare($this->regionCityProHelper->getMageVersion(), '2.4.0', '<')
                ? self::CITY_ID_SORT_ORDER_OLD : self::CITY_ID_SORT_ORDER_NEW;
        }
        $this->cityIdOrder = $sortOrder;
        return $this->cityIdOrder;
    }
}
