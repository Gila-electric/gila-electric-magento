<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Test\Integration\Controller\File\Export;

use Amasty\QuickOrder\Block\Grid\GridConfigProcessor;
use Amasty\QuickOrder\Model\Session;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for \Amasty\QuickOrder\Controller\File\Export\Grid
 */
class GridTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * TODO: update test with dynamic headers count - when products has options.
     *
     * Testing export data from quick order grid.
     *
     * @dataProvider executeDataProvider
     * @magentoAppArea frontend
     * @magentoDataFixture Amasty_QuickOrder::Test/Integration/_files/products.php
     * @param array $itemsInGrid
     * @param int $countHeaders
     * @param int $countProducts
     */
    public function testExecute(array $itemsInGrid, int $countHeaders, int $countProducts): void
    {
        /** @var Session $quickOrderSession */
        $quickOrderSession = Bootstrap::getObjectManager()->get(Session::class);
        $quickOrderSession->setItems($itemsInGrid);

        ob_start();
        $this->dispatch(GridConfigProcessor::EXPORT_URL);
        $output = ob_get_clean();

        $exportedData = $this->convertFileInfoToArray($output);
        $headers = array_shift($exportedData);// remove first elements - its headers row

        $this->assertEquals($countHeaders, count($headers));
        $this->assertEquals($countProducts, count($exportedData));
    }

    private function convertFileInfoToArray(string $fileInfo): array
    {
        $data = array_filter(array_map('trim', explode(PHP_EOL, $fileInfo)));

        $data = array_map(
            function ($row) {
                return explode(',', trim($row));
            },
            $data
        );

        return $data;
    }

    public function executeDataProvider(): array
    {
        return [
            [
                [
                    1 => [
                        'id' => 1,
                        'qty' => 1,
                        'sku' => 'quickorder-simple-1'
                    ],
                    2 => [
                        'id' => 2,
                        'qty' => 1,
                        'sku' => 'quickorder-simple-2'
                    ]
                ],
                2,
                2
            ],
            [
                [
                    1 => [
                        'id' => 1,
                        'qty' => 1,
                        'sku' => 'quickorder-simple-1'
                    ]
                ],
                2,
                1
            ]
        ];
    }
}
