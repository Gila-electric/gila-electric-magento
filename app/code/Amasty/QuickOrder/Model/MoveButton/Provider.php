<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\MoveButton;

use Amasty\QuickOrder\Controller\Item\Move\InCart;
use Amasty\QuickOrder\Model\Cart\AddProductsPool;
use Amasty\QuickOrder\Model\GetIsRequestQuoteEnabled;
use Magento\Framework\UrlInterface;

class Provider
{
    public const CHECKOUT_BUTTON = 'checkout';
    public const CART_BUTTON = 'cart';
    public const QUOTE_BUTTON = 'quote';

    public const URL_MAP = [
        AddProductsPool::FROM_GRID => [
            'cart' => 'amasty_quickorder/item_move/inCart',
            'quote' => 'amasty_quickorder/item_move/inQuote'
        ],
        AddProductsPool::FROM_CATEGORY => [
            'cart' => 'amasty_quickorder/category_item_move/inCart',
            'quote' => 'amasty_quickorder/category_item_move/inQuote'
        ]
    ];

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var GetIsRequestQuoteEnabled
     */
    private $getIsRequestQuoteEnabled;

    public function __construct(
        GetIsRequestQuoteEnabled $getIsRequestQuoteEnabled,
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->getIsRequestQuoteEnabled = $getIsRequestQuoteEnabled;
    }

    public function getButtons(string $mode, array $requestedButtons): array
    {
        $buttons = [];

        if (in_array(self::QUOTE_BUTTON, $requestedButtons)) {
            $buttons[] = $this->getRequestQuoteButton($mode);
        }
        if (in_array(self::CART_BUTTON, $requestedButtons)) {
            $buttons[] = $this->getCartButton($mode);
        }
        if (in_array(self::CHECKOUT_BUTTON, $requestedButtons)) {
            $buttons[] = $this->getCheckoutButton($mode);
        }

        return array_values(array_filter($buttons));
    }

    protected function getRequestQuoteButton(string $mode): array
    {
        if ($this->getIsRequestQuoteEnabled->execute()) {
            $button = [
                'title' => __('Add to Quote'),
                'classes' => 'amqorder-button -empty -quote',
                'url' => $this->urlBuilder->getUrl(static::URL_MAP[$mode]['quote'])
            ];
        }

        return $button ?? [];
    }

    protected function getCartButton(string $mode): array
    {
        return [
            'title' => __('Add to Cart'),
            'classes' => 'amqorder-button -empty -cart',
            'url' => $this->urlBuilder->getUrl(static::URL_MAP[$mode]['cart'])
        ];
    }

    protected function getCheckoutButton(string $mode): array
    {
        return [
            'title' => __('Checkout'),
            'classes' => 'amqorder-button -fill -primary -checkout',
            'url' => $this->urlBuilder->getUrl(static::URL_MAP[$mode]['cart'], [
                InCart::REDIRECT_ACTION => InCart::REDIRECT_IN_CHECKOUT
            ])
        ];
    }
}
