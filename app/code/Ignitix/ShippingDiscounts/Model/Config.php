<?php
namespace Ignitix\ShippingDiscounts\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    // These MUST match system.xml section/group/field structure:
    // <section id="sales">
    //   <group id="ignitix_shippingdiscounts">
    //     <field id="enabled|discount_percent|min_subtotal_incl_tax">
    private const XML_PATH_ENABLED = 'sales/ignitix_shippingdiscounts/enabled';
    private const XML_PATH_PERCENT = 'sales/ignitix_shippingdiscounts/discount_percent';
    private const XML_PATH_MIN_SUBTOTAL_INCL_TAX = 'sales/ignitix_shippingdiscounts/min_subtotal_incl_tax';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getDiscountPercent(?int $storeId = null): float
    {
        return (float)$this->scopeConfig->getValue(self::XML_PATH_PERCENT, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMinSubtotalInclTax(?int $storeId = null): float
    {
        return (float)$this->scopeConfig->getValue(self::XML_PATH_MIN_SUBTOTAL_INCL_TAX, ScopeInterface::SCOPE_STORE, $storeId);
    }
}