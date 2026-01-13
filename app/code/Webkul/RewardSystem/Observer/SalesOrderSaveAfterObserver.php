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
namespace Webkul\RewardSystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;

class SalesOrderSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $session;

    /**
     * @param SessionManager $session
     */
    public function __construct(
        SessionManager $session
    ) {
        $this->session = $session;
    }

    /**
     * Execute method
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $rewardAmount = $observer->getQuote()->getRewardAmount();
        $baseRewardAmount = $observer->getQuote()->getBaseRewardAmount();
        $order->setRewardAmount($rewardAmount);
        $order->setBaseRewardAmount($baseRewardAmount);
    }
}
