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
namespace Webkul\RewardSystem\Model\Order\Creditmemo\Total;

class Rewardamount extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * Collect method
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $orderRewardamountTotal = $order->getRewardAmount();
        $orderBaseRewardamountTotal = $order->getBaseRewardAmount();
        if ($orderRewardamountTotal && $order->getTotalRefunded() == 0) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal()+$orderRewardamountTotal);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()+$orderBaseRewardamountTotal);
        }
        return $this;
    }
}
