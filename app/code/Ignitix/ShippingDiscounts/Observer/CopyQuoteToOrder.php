<?php
namespace Ignitix\ShippingDiscounts\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CopyQuoteToOrder implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        if (!$quote || !$order) {
            return;
        }

        $address = $quote->getShippingAddress();
        if (!$address) {
            return;
        }

        $order->setData('ignitix_original_shipping_incl_tax', $address->getData('ignitix_original_shipping_incl_tax'));
        $order->setData('ignitix_base_original_shipping_incl_tax', $address->getData('ignitix_base_original_shipping_incl_tax'));
        $order->setData('ignitix_shipping_discount_amount', $address->getData('ignitix_shipping_discount_amount'));
        $order->setData('ignitix_base_shipping_discount_amount', $address->getData('ignitix_base_shipping_discount_amount'));
    }
}