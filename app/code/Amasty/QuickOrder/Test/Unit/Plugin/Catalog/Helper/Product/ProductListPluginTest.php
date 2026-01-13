<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Test\Unit\Plugin\Catalog\Helper\Product;

use Amasty\QuickOrder\Model\CategoryMode as CategoryModeProvider;
use Amasty\QuickOrder\Model\Source\CategoryMode;
use Amasty\QuickOrder\Plugin\Catalog\Helper\Product\ProductListPlugin;
use Amasty\QuickOrder\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\QuickOrder\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Helper\Product\ProductList;

/**
 * Class ProductListPluginTest
 *
 * @see ProductListPlugin
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ProductListPluginTest extends \PHPUnit\Framework\TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var ProductListPlugin
     */
    private $plugin;

    /**
     * @var ProductList
     */
    private $subject;

    public function setUp(): void
    {
        $this->plugin = $this->getObjectManager()->getObject(ProductListPlugin::class);
        $this->subject = $this->createMock(ProductList::class);
    }

    /**
     * @covers ProductListPlugin::afterGetAvailableViewMode
     *
     * @dataProvider afterGetAvailableViewModeDataProvider
     *
     * @throws \ReflectionException
     */
    public function testAfterGetAvailableViewMode(
        bool $isAvailable,
        int $replacementType,
        ?array $modes,
        ?array $expectedModes
    ) {
        $categoryMode = $this->createPartialMock(CategoryModeProvider::class, [
            'isAvailable',
            'getReplacementType'
        ]);
        $categoryMode->expects($this->once())->method('isAvailable')->willReturn($isAvailable);
        $categoryMode->expects($this->any())->method('getReplacementType')->willReturn($replacementType);

        $this->setProperty($this->plugin, 'categoryMode', $categoryMode, ProductListPlugin::class);

        $actualModes = $this->plugin->afterGetAvailableViewMode($this->subject, $modes);

        if (is_array($actualModes)) {
            $actualModes = array_keys($actualModes);
        }

        $this->assertEquals($expectedModes, $actualModes);
    }

    /**
     * @covers ProductListPlugin::beforeGetDefaultViewMode
     *
     * @dataProvider beforeGetDefaultViewModeDataProvider
     *
     * @throws \ReflectionException
     */
    public function testBeforeGetDefaultViewMode(
        bool $isAvailable,
        int $replacementType,
        ?array $options,
        ?array $expectedOptions
    ) {
        $categoryMode = $this->createPartialMock(CategoryModeProvider::class, [
            'isAvailable',
            'getReplacementType'
        ]);
        $categoryMode->expects($this->once())->method('isAvailable')->willReturn($isAvailable);
        $categoryMode->expects($this->any())->method('getReplacementType')->willReturn($replacementType);

        $this->setProperty($this->plugin, 'categoryMode', $categoryMode, ProductListPlugin::class);

        $actualOptions = $this->plugin->beforeGetDefaultViewMode($this->subject, $options);
        $actualOptions = array_shift($actualOptions);

        if (is_array($actualOptions)) {
            $actualOptions = array_keys($actualOptions);
        }

        $this->assertEquals($expectedOptions, $actualOptions);
    }

    /**
     * Data provider for afterGetAvailableViewMode test
     * @return array
     */
    public function afterGetAvailableViewModeDataProvider(): array
    {
        return [
            [
                false,
                CategoryMode::AS_DEFAULT,
                null,
                null
            ],
            [
                true,
                CategoryMode::AS_DEFAULT,
                null,
                [CategoryModeProvider::QUICKORDER_MODE]
            ],
            [
                true,
                CategoryMode::USE_DEFAULT,
                null,
                [CategoryModeProvider::QUICKORDER_MODE]
            ],
            [
                true,
                CategoryMode::AS_DEFAULT,
                null,
                [CategoryModeProvider::QUICKORDER_MODE]
            ],
            [
                true,
                CategoryMode::AS_DEFAULT,
                ['xxx' => 'xxx'],
                ['xxx', CategoryModeProvider::QUICKORDER_MODE]
            ],
            [
                true,
                CategoryMode::YES,
                ['xxx' => 'xxx'],
                [CategoryModeProvider::QUICKORDER_MODE]
            ]
        ];
    }

    /**
     * Data provider for beforeGetDefaultViewMode test
     * @return array
     */
    public function beforeGetDefaultViewModeDataProvider()
    {
        return [
            [
                false,
                CategoryMode::AS_DEFAULT,
                null,
                null
            ],
            [
                false,
                CategoryMode::USE_DEFAULT,
                ['xxx' => 'xxx'],
                ['xxx']
            ],
            [
                true,
                CategoryMode::YES,
                ['xxx' => 'xxx'],
                ['xxx']
            ],
            [
                true,
                CategoryMode::AS_DEFAULT,
                ['xxx' => 'xxx'],
                [CategoryModeProvider::QUICKORDER_MODE]
            ]
        ];
    }
}
