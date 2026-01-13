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

class AddProductsToCart
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
     * @param \Magento\QuoteGraphQl\Model\Cart\AddProductsToCart $subject
     * @param callable $proceed
     * @param Quote $cart
     * @param array $cartItems
     * @return void
     */
    public function aroundExecute(
        \Magento\QuoteGraphQl\Model\Cart\AddProductsToCart $subject,
        callable $proceed,
        Quote $cart,
        array $cartItems
    ) {
        $proceed(
            $cart,
            $cartItems
        );

        $this->helper->unsetRewardInfoInQuote($cart);
    }
}
