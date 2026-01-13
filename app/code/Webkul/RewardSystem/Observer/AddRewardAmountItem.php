<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RewardSystem\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Add AddRewardAmountItem item to Payment Cart amount.
 */
class AddRewardAmountItem implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Add Reward amount as custom item to payment cart totals.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Payment\Model\Cart $cart */
        $cart = $observer->getEvent()->getCart();
        $quote = $this->checkoutSession->getQuote();
        $rewardAmount = $quote->getRewardAmount();
        $cart->addCustomItem(__('Reward Amount'), 1, 1.00 * $rewardAmount, 'rewardamount');
    }
}
