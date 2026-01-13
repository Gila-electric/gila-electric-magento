<?php
namespace Ignitix\ShippingDiscounts\Model\Quote\Total;

use Ignitix\ShippingDiscounts\Model\Config;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class ShippingDiscount extends AbstractTotal
{
    public const TOTAL_CODE = 'ignitix_shipping_discount';

    public function __construct(
        private readonly Config $config,
        private readonly PriceCurrencyInterface $priceCurrency
    ) {
        $this->setCode(self::TOTAL_CODE);
    }

    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): self {
        parent::collect($quote, $shippingAssignment, $total);

        $address = $shippingAssignment->getShipping()->getAddress();
        $storeId = (int)$quote->getStoreId();

        // Reset stored values (avoid stale display)
        $address->setData('ignitix_original_shipping_incl_tax', null);
        $address->setData('ignitix_base_original_shipping_incl_tax', null);
        $address->setData('ignitix_shipping_discount_amount', null);
        $address->setData('ignitix_base_shipping_discount_amount', null);
        $total->setData('ignitix_shipping_discount_amount', 0.0);
        $total->setData('ignitix_base_shipping_discount_amount', 0.0);

        if (!$this->config->isEnabled($storeId)) {
            return $this;
        }

        $percent = (float)$this->config->getDiscountPercent($storeId);
        if ($percent <= 0.0) {
            return $this;
        }
        if ($percent > 100.0) {
            $percent = 100.0;
        }

        // Only Table Rates (carrier = tablerate). Method might not always be "bestway".
        $shippingMethod = (string)$address->getShippingMethod(); // e.g. tablerate_bestway
        if ($shippingMethod === '' || strpos($shippingMethod, 'tablerate_') !== 0) {
            return $this;
        }

        // Min subtotal incl tax (after discounts)
        $minSubtotalInclTax = (float)$this->config->getMinSubtotalInclTax($storeId);
        if ($minSubtotalInclTax > 0.0) {
            $subtotalInclTax = (float)($total->getSubtotalInclTax() ?: 0.0);
            $discountAmount = (float)($total->getDiscountAmount() ?: 0.0); // negative

            // Fallback if subtotalInclTax isn't present in some setups
            if ($subtotalInclTax <= 0.0 && (float)$total->getSubtotal() > 0.0) {
                $itemsTax = (float)$total->getTaxAmount() - (float)$total->getShippingTaxAmount();
                $subtotalInclTax = (float)$total->getSubtotal() + max(0.0, $itemsTax);
            }

            $subtotalInclTaxAfterDiscounts = max(0.0, $subtotalInclTax + $discountAmount);

            if ($subtotalInclTaxAfterDiscounts + 0.0001 < $minSubtotalInclTax) {
                return $this;
            }
        }

        // Shipping amounts (incl tax) - discount is applied AFTER shipping tax
        $shippingExcl = (float)$total->getShippingAmount();
        $shippingTax  = (float)$total->getShippingTaxAmount();
        $shippingIncl = (float)($total->getShippingInclTax() ?: ($shippingExcl + $shippingTax));

        $baseShippingExcl = (float)$total->getBaseShippingAmount();
        $baseShippingTax  = (float)$total->getBaseShippingTaxAmount();
        $baseShippingIncl = (float)($total->getBaseShippingInclTax() ?: ($baseShippingExcl + $baseShippingTax));

        if ($shippingIncl <= 0.0 || $baseShippingIncl <= 0.0) {
            return $this;
        }

        $origIncl = $shippingIncl;
        $baseOrigIncl = $baseShippingIncl;

        $discountIncl = $this->priceCurrency->round($origIncl * ($percent / 100.0));
        $baseDiscountIncl = $this->priceCurrency->round($baseOrigIncl * ($percent / 100.0));

        if ($discountIncl <= 0.0 || $baseDiscountIncl <= 0.0) {
            return $this;
        }

        $newIncl = max(0.0, $origIncl - $discountIncl);
        $baseNewIncl = max(0.0, $baseOrigIncl - $baseDiscountIncl);

        // Split back into excl+tax proportionally to keep totals consistent
        $ratioTax = ($origIncl > 0.0) ? ($shippingTax / $origIncl) : 0.0;
        $baseRatioTax = ($baseOrigIncl > 0.0) ? ($baseShippingTax / $baseOrigIncl) : 0.0;

        $newTax = $this->priceCurrency->round($newIncl * $ratioTax);
        $newExcl = $this->priceCurrency->round($newIncl - $newTax);
        $newInclFinal = $newExcl + $newTax;

        $baseNewTax = $this->priceCurrency->round($baseNewIncl * $baseRatioTax);
        $baseNewExcl = $this->priceCurrency->round($baseNewIncl - $baseNewTax);
        $baseNewInclFinal = $baseNewExcl + $baseNewTax;

        $deltaTax = $newTax - $shippingTax;
        $deltaIncl = $newInclFinal - $origIncl;

        $baseDeltaTax = $baseNewTax - $baseShippingTax;
        $baseDeltaIncl = $baseNewInclFinal - $baseOrigIncl;

        // Update shipping totals
        $total->setShippingAmount($newExcl);
        $total->setShippingTaxAmount($newTax);
        $total->setShippingInclTax($newInclFinal);

        $total->setBaseShippingAmount($baseNewExcl);
        $total->setBaseShippingTaxAmount($baseNewTax);
        $total->setBaseShippingInclTax($baseNewInclFinal);

        // Update overall tax + grand totals to match
        $total->setTaxAmount((float)$total->getTaxAmount() + $deltaTax);
        $total->setBaseTaxAmount((float)$total->getBaseTaxAmount() + $baseDeltaTax);

        $total->setGrandTotal((float)$total->getGrandTotal() + $deltaIncl);
        $total->setBaseGrandTotal((float)$total->getBaseGrandTotal() + $baseDeltaIncl);

        // Persist on address + total (for display/API)
        $address->setData('ignitix_original_shipping_incl_tax', $origIncl);
        $address->setData('ignitix_base_original_shipping_incl_tax', $baseOrigIncl);
        $address->setData('ignitix_shipping_discount_amount', $discountIncl);
        $address->setData('ignitix_base_shipping_discount_amount', $baseDiscountIncl);

        $total->setData('ignitix_shipping_discount_amount', $discountIncl);
        $total->setData('ignitix_base_shipping_discount_amount', $baseDiscountIncl);

        return $this;
    }

    public function fetch(Quote $quote, Total $total): ?array
    {
        $discount = (float)$total->getData('ignitix_shipping_discount_amount');
        if ($discount <= 0.0) {
            return null;
        }

        return [
            'code'  => self::TOTAL_CODE,
            'title' => __('Shipping Discount'),
            'value' => -1 * $discount,
        ];
    }
}