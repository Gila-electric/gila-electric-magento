<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Item\Move;

use Amasty\QuickOrder\Model\Cart\AddProductsPool;
use Magento\Checkout\Helper\Cart as CartHelper;

class InCart extends AbstractAction
{
    public const REDIRECT_ACTION = 'redirect_action';

    public const REDIRECT_IN_CART = 'checkout/cart';
    public const REDIRECT_IN_CHECKOUT = 'checkout';

    /**
     * @return string
     */
    public function getRedirectAction(): string
    {
        if ($this->getMode() === AddProductsPool::FROM_CATEGORY) {
            $redirectAction = $this->getCartHelper()->getShouldRedirectToCart() ? self::REDIRECT_IN_CART : '';
        } else {
            $redirectAction = $this->getRequest()->getParam(static::REDIRECT_ACTION) == self::REDIRECT_IN_CHECKOUT
                ? self::REDIRECT_IN_CHECKOUT
                : self::REDIRECT_IN_CART;
        }

        return $redirectAction;
    }

    private function getCartHelper(): CartHelper
    {
        return $this->getData('cartHelper');
    }
}
