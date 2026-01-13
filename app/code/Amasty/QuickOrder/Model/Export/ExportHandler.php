<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Export;

use Amasty\QuickOrder\Model\Export\Provider\OptionProvider;
use Amasty\QuickOrder\Model\Import\ImportHandler;
use Amasty\QuickOrder\Model\Import\Provider\Bundle\Provider as BundleProvider;
use Amasty\QuickOrder\Model\Import\Provider\Configurable\Provider as ConfigurableProvider;
use Amasty\QuickOrder\Model\Import\Provider\CustomOption as CustomOptionProvider;
use Amasty\QuickOrder\Model\Import\Provider\Grouped\Provider as GroupedProvider;
use Amasty\QuickOrder\Model\Item\Manager as ItemManager;

class ExportHandler
{
    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var OptionProvider[]
     */
    private $optionProviders;

    /**
     * @var ExportDataFactory
     */
    private $exportDataFactory;

    public function __construct(
        ExportDataFactory $exportDataFactory,
        ItemManager $itemManager,
        array $optionProviders
    ) {
        $this->itemManager = $itemManager;
        $this->optionProviders = $optionProviders;
        $this->exportDataFactory = $exportDataFactory;
    }

    public function getExportData(): ExportData
    {
        /** @var ExportData $exportData */
        $exportData = $this->exportDataFactory->create();

        $allItems = $this->itemManager->getAllItems();

        $this->initOptionProviders(array_column($allItems, 'sku'));

        $exportData->addHeader(ImportHandler::SKU_FIELD);
        $exportData->addHeader(ImportHandler::QTY_FIELD);

        foreach ($allItems as $item) {
            $rowData = [
                $item[ImportHandler::SKU_FIELD],
                $item[ImportHandler::QTY_FIELD]
            ];

            foreach ($this->retrieveOptions($item) as $optionPosition => $option) {
                $exportData->addOptionHeader($optionPosition + 1);
                $rowData[] = $option;
            }

            $exportData->addRow($rowData);
        }

        return $exportData;
    }

    private function retrieveOptions(array $item): array
    {
        $options = [];

        foreach ($this->optionProviders as $providerType => $optionProvider) {
            $optionRequestCode = $this->getRequestCode($providerType);
            $qtyRequestCode = $this->getQtyRequestCode($providerType);

            if (isset($item[$optionRequestCode]) && is_array($item[$optionRequestCode])) {
                foreach ($item[$optionRequestCode] as $optionId => $valueId) {
                    $optionTitle = $optionProvider->getOption((int) $optionId);
                    $valueTitle = $optionProvider->getValue((string) $valueId);
                    if ($optionTitle && $valueTitle) {
                        $option = sprintf('%s:%s', $optionTitle, $valueTitle);
                        if ($qtyRequestCode && isset($item[$qtyRequestCode][$optionId])) {
                            $option .= sprintf(':%s', $item[$qtyRequestCode][$optionId]);
                        }
                        $options[] = $option;
                    }
                }
            }
        }

        return $options;
    }

    private function getRequestCode(string $providerType): string
    {
        switch ($providerType) {
            case BundleProvider::TYPE:
                $optionRequestCode = BundleProvider::REQUEST_CODE;
                $qtyRequestCode = BundleProvider::QTY_REQUEST_CODE;
                break;
            case ConfigurableProvider::TYPE:
                $optionRequestCode = ConfigurableProvider::REQUEST_CODE;
                break;
            case CustomOptionProvider::TYPE:
                $optionRequestCode = CustomOptionProvider::REQUEST_CODE;
                break;
            case GroupedProvider::TYPE:
                $optionRequestCode = GroupedProvider::REQUEST_CODE;
                break;
            default:
                $optionRequestCode = '';
        }

        return $optionRequestCode;
    }

    private function getQtyRequestCode(string $providerType): string
    {
        switch ($providerType) {
            case BundleProvider::TYPE:
                $qtyRequestCode = BundleProvider::QTY_REQUEST_CODE;
                break;
            default:
                $qtyRequestCode = '';
        }

        return $qtyRequestCode;
    }

    private function initOptionProviders(array $skuArray)
    {
        foreach ($this->optionProviders as $optionProvider) {
            $optionProvider->initData($skuArray);
        }
    }
}
