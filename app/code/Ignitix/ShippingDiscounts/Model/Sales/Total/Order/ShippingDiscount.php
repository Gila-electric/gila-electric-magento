<?php
namespace Ignitix\ShippingDiscounts\Model\Sales\Total\Order;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Total\AbstractTotal;

class ShippingDiscount extends AbstractTotal
{
    public function fetch(Order $order): ?array
    {
        $discount = (float)$order->getData('ignitix_shipping_discount_amount');
        if ($discount <= 0.0) {
            return null;
        }

        return [
            'code'  => 'ignitix_shipping_discount',
            'title' => __('Shipping Discount'),
            'value' => -1 * $discount,
        ];
    }
}