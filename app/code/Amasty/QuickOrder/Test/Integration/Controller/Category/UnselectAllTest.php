<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Test\Integration\Controller\Category;

use Amasty\QuickOrder\Block\Category\Grid\ToolbarConfigProcessor;
use Amasty\QuickOrder\Model\Category\Item\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\TestFramework\Helper\Bootstrap;
use Zend\Http\Response;

/**
 * Test class for \Amasty\QuickOrder\Controller\Category\UnselectAll
 */
class UnselectAllTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Testing for enabled category mode and disabled customer group with ID 1.
     *
     * @dataProvider executeDataProvider
     * @magentoAppArea frontend
     * @magentoConfigFixture default_store amasty_quickorder/category_mode/enabled 1
     * @magentoConfigFixture default_store amasty_quickorder/category_mode/disabled_customer_groups 1
     * @param array $itemsInSession
     * @param string $methodType
     * @param int $customerGroupId
     * @param int $expectedCounter
     * @param int $expectedStatus
     */
    public function testExecute(
        array $itemsInSession,
        string $methodType,
        int $customerGroupId,
        int $expectedCounter,
        int $expectedStatus
    ): void {
        $this->getRequest()->setMethod($methodType);
        /** @var Session $categoryItemSession */
        $categoryItemSession = Bootstrap::getObjectManager()->create(Session::class);
        $categoryItemSession->setItems($itemsInSession);

        /** @var CustomerSession $customerSession */
        $customerSession = Bootstrap::getObjectManager()->create(CustomerSession::class);
        $customerSession->setCustomerGroupId($customerGroupId);

        $this->dispatch(ToolbarConfigProcessor::CLEAR_URL);

        $this->assertEquals($expectedStatus, $this->getResponse()->getStatusCode());
        $this->assertEquals($expectedCounter, count($categoryItemSession->getItems()));
    }

    /**
     * Data provider for execute test
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            [
                [[], [], []],
                'get',
                2,
                3,
                Response::STATUS_CODE_400
            ],
            [
                [[], [], []],
                'post',
                2,
                0,
                Response::STATUS_CODE_200
            ],
            [
                [[], [], []],
                'post',
                1,
                3,
                Response::STATUS_CODE_403
            ]
        ];
    }
}
