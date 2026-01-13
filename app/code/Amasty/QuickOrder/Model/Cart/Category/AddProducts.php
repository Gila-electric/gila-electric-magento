<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Cart\Category;

use Amasty\QuickOrder\Model\Cart\AddProductsInterface;
use Amasty\QuickOrder\Model\Cart\Result as CartResult;
use Amasty\QuickOrder\Model\Cart\ResultFactory as CartResultFactory;
use Amasty\QuickOrder\Model\Category\Item\Manager;
use Amasty\RequestQuote\Model\Cart as QuoteCart;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Cart\CartInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

class AddProducts implements AddProductsInterface
{
    public const PRODUCT_TEMPLATE = '<a href="%s" title="%2$s">%2$s</a>';
    /**
     * @var CartInterface|Cart|QuoteCart
     */
    private $cart;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Manager
     */
    private $itemManager;

    /**
     * @var CartResultFactory
     */
    private $cartResultFactory;

    public function __construct(
        CartInterface $cart,
        Manager $itemManager,
        CartResultFactory $cartResultFactory,
        ManagerInterface $messageManager
    ) {
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        $this->itemManager = $itemManager;
        $this->cartResultFactory = $cartResultFactory;
    }

    /**
     * Try adding products from quick order grid into amasty quote cart / magento cart.
     * In error case - return products with errors.
     *
     * @return CartResult
     */
    public function execute(): CartResult
    {
        /** @var CartResult $cartResult */
        $cartResult = $this->cartResultFactory->create();

        $addedProducts = [];
        $notAddedProducts = [];

        $items = $this->itemManager->getItems();

        foreach ($items as $itemData) {
            try {
                $this->cart->addProduct($itemData['product_id'], $itemData);
                $cartResult->addProduct($itemData['product_id']);
                $addedProducts[] = [
                    'product_name' => $itemData['product_name'],
                    'product_url' => $itemData['product_url']
                ];
            } catch (LocalizedException $e) {
                $notAddedProducts[] = [
                    'product_name' => $itemData['product_name'],
                    'product_url' => $itemData['product_url']
                ];
                continue;
            }
        }

        $this->cart->save();
        $this->itemManager->clear();

        if ($addedProducts) {
            $this->messageManager->addComplexSuccessMessage('quickorderAddedProducts', [
                'items' => $this->getItemsNames($addedProducts)
            ]);
        }
        if ($notAddedProducts) {
            $this->messageManager->addComplexErrorMessage('quickorderNotAddedProducts', [
                'items' => $this->getItemsNames($notAddedProducts)
            ]);
        }

        return $cartResult;
    }

    private function getItemsNames(array $items): string
    {
        $result = '';

        foreach ($items as $key => $itemData) {
            $result .= ', ';
            $result .= sprintf(static::PRODUCT_TEMPLATE, $itemData['product_url'], $itemData['product_name']);
        }

        return trim($result, ', ');
    }
}
