<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Test\Unit\Model;

use Amasty\QuickOrder\Model\CategoryMode;
use Amasty\QuickOrder\Model\ConfigProvider;
use Amasty\QuickOrder\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\QuickOrder\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\Http as Request;

/**
 * Class CategoryModeTest
 *
 * @see CategoryMode
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CategoryModeTest extends \PHPUnit\Framework\TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers CategoryMode::isAvailable
     *
     * @dataProvider isAvailableDataProvider
     *
     * @throws \ReflectionException
     */
    public function testIsAvailable(
        bool $tableModeEnabled,
        bool $isGroupEnabled,
        string $fullActionName,
        bool $isEnabledOnSearch,
        bool $isCategoryEnabled,
        bool $expectedResult
    ) {
        $configProvider = $this->createMock(ConfigProvider::class);
        $customerSession = $this->createMock(CustomerSession::class);
        $layerResolver = $this->createMock(Resolver::class);
        $layer = $this->createMock(Layer::class);
        $request = $this->createMock(Request::class);
        $category = $this->createMock(Category::class);

        $configProvider->expects($this->once())->method('isTableModeEnabled')->willReturn($tableModeEnabled);

        $configProvider->expects($this->any())->method('isGroupEnabledForTableMode')->willReturn($isGroupEnabled);

        $configProvider->expects($this->any())->method('isTableModeEnabledOnSearch')->willReturn($isEnabledOnSearch);

        $layer->expects($this->any() )->method('getCurrentCategory')->willReturn($category);
        $category->expects($this->any())->method('getId')->willReturn(0);
        $configProvider->expects($this->any())->method('isCategoryEnabledForTableMode')->willReturn($isCategoryEnabled);

        $request->expects($this->any())->method('getFullActionName')->willReturn($fullActionName);

        $customerSession->expects($this->any())->method('getCustomerGroupId')->willReturn(0);

        $layerResolver->expects($this->once())->method('get')->willReturn($layer);

        $model = $this->getObjectManager()->getObject(CategoryMode::class, [
            'customerSession' => $customerSession,
            'configProvider' => $configProvider,
            'layerResolver' => $layerResolver,
            'request' => $request
        ]);

        $actualResult = $model->isAvailable();

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Data provider for isAvailable test
     * @return array
     */
    public function isAvailableDataProvider(): array
    {
        return [
            [
                true,
                true,
                CategoryMode::SEARCH_ACTION_PAGE,
                true,
                false,
                true
            ],
            [
                false,
                true,
                CategoryMode::SEARCH_ACTION_PAGE,
                true,
                false,
                false
            ],
            [
                true,
                false,
                CategoryMode::SEARCH_ACTION_PAGE,
                true,
                false,
                false
            ],
            [
                true,
                true,
                '',
                false,
                true,
                true
            ],
            [
                true,
                true,
                CategoryMode::SEARCH_ACTION_PAGE,
                false,
                true,
                false
            ],
            [
                true,
                true,
                '',
                false,
                true,
                true
            ],
            [
                true,
                true,
                '',
                false,
                false,
                false
            ]
        ];
    }
}
