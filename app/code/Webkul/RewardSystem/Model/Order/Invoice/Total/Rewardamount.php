<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RewardSystem\Model\Order\Invoice\Total;

class Rewardamount extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * Collect method
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $orderRewardamountTotal = $order->getRewardAmount();
        $orderBaseRewardamountTotal = $order->getBaseRewardAmount();
        if ($orderRewardamountTotal && count($order->getInvoiceCollection())==0) {
            $invoice->setGrandTotal($invoice->getGrandTotal()+$orderRewardamountTotal);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$orderBaseRewardamountTotal);
        }
        return $this;
    }
}
