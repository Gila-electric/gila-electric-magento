<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_RewardSystem
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\RewardSystem\Plugin\GraphQl\Checkout;

use Webkul\RewardSystem\Helper\Data as RewardHelper;
use Magento\Quote\Model\Quote;

class UpdateCartItem
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var RewardHelper;
     */
    protected $helper;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param RewardHelper $helper
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        RewardHelper $helper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helper;
    }

    /**
     * Around execute
     *
     * @param \Magento\QuoteGraphQl\Model\Cart\UpdateCartItem $subject
     * @param callable $proceed
     * @param Quote $cart
     * @param integer $cartItemId
     * @param float $quantity
     * @param array $customizableOptionsData
     * @return void
     */
    public function aroundExecute(
        \Magento\QuoteGraphQl\Model\Cart\UpdateCartItem $subject,
        callable $proceed,
        Quote $cart,
        int $cartItemId,
        float $quantity,
        array $customizableOptionsData
    ) {
        $proceed(
            $cart,
            $cartItemId,
            $quantity,
            $customizableOptionsData
        );

        $this->helper->unsetRewardInfoInQuote($cart);
    }
}
