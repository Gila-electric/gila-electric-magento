<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Test\Integration\Controller\Category;

use Amasty\QuickOrder\Block\Category\Grid\GridConfigProcessor;
use Amasty\QuickOrder\Controller\Category\UpdateItem;
use Amasty\QuickOrder\Model\Category\Item\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Helper\Bootstrap;
use Zend\Http\Response;

/**
 * Test class for \Amasty\QuickOrder\Controller\Category\UpdateItem
 */
class UpdateItemTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Testing for enabled category mode and disabled customer group with ID 1.
     *
     * @dataProvider executeDataProvider
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store amasty_quickorder/category_mode/enabled 1
     * @magentoConfigFixture default_store amasty_quickorder/category_mode/disabled_customer_groups 1
     * @magentoDataFixture Amasty_QuickOrder::Test/Integration/_files/products.php
     * @param array $itemsInSession
     * @param int $targetItemId
     * @param string $targetItemData
     * @param string $methodType
     * @param int $customerGroupId
     * @param int $expectedCounter
     * @param int $expectedStatus
     */
    public function testExecute(
        array $itemsInSession,
        int $targetItemId,
        string $targetItemData,
        string $methodType,
        int $customerGroupId,
        int $expectedCounter,
        int $expectedStatus
    ): void {
        [$itemsInSession, $targetItemId] = $this->updateItemsData($itemsInSession, $targetItemId);

        /** @var Session $categoryItemSession */
        $categoryItemSession = Bootstrap::getObjectManager()->create(Session::class);
        $categoryItemSession->setItems($itemsInSession);

        /** @var CustomerSession $customerSession */
        $customerSession = Bootstrap::getObjectManager()->create(CustomerSession::class);
        $customerSession->setCustomerGroupId($customerGroupId);

        $this->getRequest()->setMethod($methodType);
        $this->getRequest()->setParam(UpdateItem::ITEM_ID_PARAM, $targetItemId);
        $this->getRequest()->setParam(UpdateItem::ITEM_DATA_PARAM, $targetItemData);

        $this->dispatch(GridConfigProcessor::UPDATE_URL);

        $this->assertEquals($expectedStatus, $this->getResponse()->getStatusCode());
        $this->assertEquals($expectedCounter, count($categoryItemSession->getItems()));
    }

    /**
     * Replace unique item id with product id.
     * Because on table mode product id - is unique field.
     *
     * @param array $itemsInSession
     * @param int $targetItemId
     * @return array
     */
    private function updateItemsData(array $itemsInSession, int $targetItemId): array
    {
        $categoryItems = [];

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

        foreach ($itemsInSession as $item) {
            try {
                $productId = (int)$productRepository->get($item['sku'])->getId();
                if ($item['id'] === $targetItemId) {
                    $targetItemId = $productId;
                }
                $item['id'] = $productId;
                $item['product_id'] = $productId;
                $categoryItems[$productId] = $item;
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return [$categoryItems, $targetItemId];
    }

    /**
     * Data provider for execute test
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            [
                [
                    [
                        'id' => 1,
                        'qty' => 1,
                        'checked' => 0,
                        'sku' => 'quickorder-simple-1'
                    ]
                ],
                1,
                'qty=1&checked=1',
                'get',
                2,
                1,
                Response::STATUS_CODE_200
            ],
            [
                [
                    [
                        'id' => 1,
                        'qty' => 1,
                        'checked' => 1,
                        'sku' => 'quickorder-simple-1'
                    ]
                ],
                1,
                'qty=1&checked=0',
                'get',
                2,
                0,
                Response::STATUS_CODE_200
            ],
            [
                [
                    [
                        'id' => 1,
                        'qty' => 1,
                        'checked' => 1,
                        'sku' => 'quickorder-simple-1'
                    ]
                ],
                1,
                'qty=1&checked=0',
                'get',
                1,
                1,
                Response::STATUS_CODE_403
            ]
        ];
    }
}
