<?php
namespace Accept\Payments\Helper;

class OriginPrice
{
    protected $order;
    protected $quote;
    public function __construct(
        \Magento\Sales\Model\Order $order,
        \Magento\Quote\Model\Quote $quote
    ) {
        $this->order                = $order;
        $this->quote                = $quote;
    }

    public function setOriginPrice($orderId)
    {
        $customizedOrder = $this->order->load($orderId);
        $quote = $this->quote->load($customizedOrder->getQuoteId());
        $items = $quote->getAllItems();
        $calcItemsPrice = 0;
        foreach ($items as $quoteItem) {
            $origOrderItem = $customizedOrder->getItemByQuoteItemId($quoteItem->getId());
            $islam = $origOrderItem->getOriginalPrice();
            $qty = $origOrderItem->getQtyOrdered();
            $totalOrderItemPrice = $qty * $islam;
            $origOrderItem->setPrice($islam);
            $origOrderItem->setRowTotal($totalOrderItemPrice);
            $origOrderItem->getProduct()->setIsSuperMode(true);
            $origOrderItem->save();
            $calcItemsPrice += $totalOrderItemPrice;
        }
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $quote->save();
        $shippingAmount = (float)$customizedOrder->getShippingAmount();
        $discountAmount = (float)$customizedOrder->getDiscountAmount();
        $customizedOrder->setSubtotal($calcItemsPrice)
            ->setBaseSubtotal($calcItemsPrice)
            ->setGrandTotal($calcItemsPrice + $shippingAmount + $discountAmount)
            ->setBaseGrandTotal($calcItemsPrice + $shippingAmount + $discountAmount);
        $customizedOrder->save();
        return $customizedOrder;
    }
}