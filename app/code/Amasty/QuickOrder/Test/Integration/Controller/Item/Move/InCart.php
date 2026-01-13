<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Test\Integration\Controller\Item\Move;

use Amasty\QuickOrder\Controller\Item\Move\AbstractAction as MoveAction;
use Amasty\QuickOrder\Model\Cart\AddProductsPool;
use Amasty\QuickOrder\Model\Category\Item\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Amasty\QuickOrder\Controller\Item\Move\InCart
 */
class InCart extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Testing adding products in cart via category item session.
     *
     * @dataProvider executeDataProvider
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store amasty_quickorder/general/enabled 1
     * @magentoDataFixture Amasty_QuickOrder::Test/Integration/_files/products.php
     * @param string $methodType
     * @param array $itemsInSession
     * @param int $expectedAddedProducts Counter of successfully added in cart.
     */
    public function testExecute(string $methodType, array $itemsInSession, int $expectedAddedProducts): void
    {
        /** @var Session $categoryItemSession */
        $categoryItemSession = Bootstrap::getObjectManager()->create(Session::class);
        $categoryItemSession->setItems($this->updateItemsData($itemsInSession));

        $this->getRequest()->setMethod($methodType);
        $this->getRequest()->setParam(MoveAction::ADD_PRODUCTS_MODE, AddProductsPool::FROM_CATEGORY);

        $this->dispatch('amasty_quickorder/category_item_move/inCart');

        /** @var Cart $cart */
        $cart = Bootstrap::getObjectManager()->get(Cart::class);

        $this->assertEquals($expectedAddedProducts, $cart->getItemsCount());
    }

    /**
     * Replace unique item id with product id.
     * Because on table mode product id - is unique field.
     *
     * @param array $itemsInSession
     * @return array
     */
    private function updateItemsData(array $itemsInSession): array
    {
        $categoryItems = [];

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

        foreach ($itemsInSession as $item) {
            try {
                $product = $productRepository->get($item['sku']);
                $productId = (int) $product->getId();
                $item['id'] = $productId;
                $item['product_id'] = $productId;
                $item['product_name'] = $product->getName();
                $item['product_url'] = $product->getProductUrl();
                $categoryItems[$productId] = $item;
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return $categoryItems;
    }

    public function executeDataProvider(): array
    {
        return [
            [
                'post',
                [
                    [
                        'id' => 1,
                        'qty' => 1,
                        'sku' => 'quickorder-simple-1'
                    ],
                    [
                        'id' => 1,
                        'qty' => 25, // request more than available
                        'sku' => 'quickorder-simple-2'
                    ],
                    [
                        'id' => 1,
                        'qty' => 1,
                        'sku' => 'quickorder-simple-3' // out of stock
                    ]
                ],
                2
            ]
        ];
    }
}
