<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Import;

use Amasty\QuickOrder\Api\Import\ProviderInterface;
use Amasty\QuickOrder\Api\Source\SourceReaderInterface;
use Amasty\QuickOrder\Model\Import\Output\Result as OutputResult;
use Amasty\QuickOrder\Model\Import\Output\ResultFactory as OutputResultFactory;
use Amasty\QuickOrder\Model\Import\Provider\Bundle\Provider as BundleProvider;
use Amasty\QuickOrder\Model\Import\Provider\Configurable\Provider as ConfigurableProvider;
use Amasty\QuickOrder\Model\Import\Provider\CustomOption;
use Amasty\QuickOrder\Model\Import\Provider\Grouped\Provider as GroupedProvider;
use Amasty\QuickOrder\Model\ImportExport\Resources\GetProductTypes;
use Amasty\QuickOrder\Model\ImportExport\Resources\Grouped\GetLinkedProducts;
use Amasty\QuickOrder\Model\Item\Manager as ItemManager;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Psr\Log\LoggerInterface;

class ImportHandler
{
    public const SKU_FIELD = 'sku';

    public const QTY_FIELD = 'qty';

    public const POSITION_FIELD = 'position';

    public const PERMANENT_FIELDS = [
        self::SKU_FIELD,
        self::QTY_FIELD
    ];

    /**
     * @var GetProductTypes
     */
    private $getProductTypes;

    /**
     * @var GetLinkedProducts
     */
    private $getLinkedProducts;

    /**
     * @var ProviderInterface[]
     */
    private $optionProviders;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputResultFactory
     */
    private $outputResultFactory;

    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var array
     */
    private $linkedProducts = [];

    public function __construct(
        GetProductTypes $getProductTypes,
        GetLinkedProducts $getLinkedProducts,
        OutputResultFactory $outputResultFactory,
        ItemManager $itemManager,
        LoggerInterface $logger,
        array $optionProviders
    ) {
        $this->getProductTypes = $getProductTypes;
        $this->getLinkedProducts = $getLinkedProducts;
        $this->optionProviders = $optionProviders;
        $this->logger = $logger;
        $this->outputResultFactory = $outputResultFactory;
        $this->itemManager = $itemManager;
    }

    /**
     * @param SourceReaderInterface $sourceReader
     * @return array
     */
    public function execute(SourceReaderInterface $sourceReader): array
    {
        $errors = [];
        $outputResult = $this->readData($sourceReader);
        if ($outputResult->getSkuArray()) {
            $data = $outputResult->getPermanentData();
            $this->parseOptions($data, $outputResult->getProductOptions(), $outputResult->getSkuArray());
            $errors = $this->itemManager->addItems($data);
        }

        return $errors;
    }

    /**
     * @param SourceReaderInterface $sourceReader
     * @return OutputResult
     */
    private function readData(SourceReaderInterface $sourceReader): OutputResult
    {
        $skuArray = $data = $productOptions = [];

        $position = 0;

        while ($row = $sourceReader->readRow()) {
            if (!isset($row[self::SKU_FIELD]) || !$row[self::SKU_FIELD]) {
                throw new LocalizedException(
                    __('The contents of the file is incorrect.'
                        . ' Please refer to the sample file to learn the right file markup.')
                );
            }

            $skuArray[] = $row[self::SKU_FIELD];
            $tmpData = [
                self::POSITION_FIELD => ++$position
            ];
            foreach (self::PERMANENT_FIELDS as $permanentField) {
                if (!isset($row[$permanentField])) {
                    continue 2;
                }
                $tmpData[$permanentField] = $row[$permanentField];
                unset($row[$permanentField]);
            }
            $data[] = $tmpData;
            $productOptions[] = $row;
        }

        /** @var OutputResult $outputResult */
        $outputResult = $this->outputResultFactory->create();
        $outputResult->setSkuArray(array_unique($skuArray));
        $outputResult->setPermanentData($data);
        $outputResult->setProductOptions($productOptions);

        return $outputResult;
    }

    /**
     * @param array $data
     * @param array $productOptions
     * @param array $skuArray
     */
    private function parseOptions(array &$data, array $productOptions, array $skuArray)
    {
        $this->initOptionProviders($skuArray);
        $productTypes = $this->getProductTypes->execute($skuArray);
        if (in_array(Grouped::TYPE_CODE, $productTypes)) {
            $this->initLinkedProducts($skuArray);
        }

        foreach ($productOptions as $index => $options) {
            $currentSku = $data[$index][self::SKU_FIELD];
            if (!isset($productTypes[$currentSku])) {
                continue;
            }
            $productType = $productTypes[$currentSku];
            $data[$index] = $this->tryAddOptions(
                $data[$index],
                $currentSku,
                $productType,
                $options
            );
        }

        $this->clearLinkedProducts();
    }

    private function tryAddOptions(
        array $currentData,
        string $currentSku,
        string $currentType,
        array $originalOptions
    ): array {
        $parsedOptions = $this->convertOptions($originalOptions, $currentSku, $this->getOptionProviders($currentType));

        switch ($currentType) {
            case Bundle::TYPE_CODE:
                if (isset($parsedOptions[BundleProvider::REQUEST_CODE])) {
                    foreach ($parsedOptions[BundleProvider::REQUEST_CODE] as $optionId => $productId) {
                        if ($parsedOptions[BundleProvider::QTY_REQUEST_CODE][$optionId] === 1) {
                            $parsedOptions[BundleProvider::QTY_REQUEST_CODE][$optionId] = $currentData[self::QTY_FIELD];
                        }
                    }
                }
                break;
            case Grouped::TYPE_CODE:
                if (isset($parsedOptions[GroupedProvider::REQUEST_CODE])) {
                    foreach ($parsedOptions[GroupedProvider::REQUEST_CODE] as $optionId => $optionQty) {
                        if ($optionQty == 0) {
                            $parsedOptions[GroupedProvider::REQUEST_CODE][$optionId] = $currentData[self::QTY_FIELD];
                        }
                    }
                    $linkedProducts = $this->linkedProducts[$currentSku] ?? [];
                    foreach ($linkedProducts as $childId) {
                        if (!isset($parsedOptions[GroupedProvider::REQUEST_CODE][$childId])) {
                            $parsedOptions[GroupedProvider::REQUEST_CODE][$childId] = 0;
                        }
                    }
                }
                break;
        }

        return array_merge(
            $currentData,
            $parsedOptions
        );
    }

    /**
     * @param array $skuArray
     */
    private function initOptionProviders(array $skuArray)
    {
        /** @var ProviderInterface $optionProvider */
        foreach ($this->optionProviders as $optionProvider) {
            $optionProvider->initData($skuArray);
        }
    }

    private function initLinkedProducts(array $skuArray): void
    {
        $this->linkedProducts = $this->getLinkedProducts->execute($skuArray);
    }

    private function clearLinkedProducts(): void
    {
        $this->linkedProducts = [];
    }

    /**
     * @param array $options
     * @param string $currentSku
     * @param ProviderInterface[] $optionProviders
     * @return array
     */
    private function convertOptions(array $options, string $currentSku, array $optionProviders): array
    {
        $convertedOptions = [];
        foreach ($options as $option) {
            [$optionTitle, $valueTitle, $qty] = $this->parseOption($option);

            foreach ($optionProviders as $optionProvider) {
                $optionId = $optionProvider->getOption($optionTitle, $currentSku);
                $valueId = $optionProvider->getValue($valueTitle, $currentSku, $optionId);
                if ($optionId !== null && $valueId !== null) {
                    $convertedOptions[$optionProvider->getCode()][$optionId] = $valueId;

                    if ($optionProvider->getCode() === BundleProvider::REQUEST_CODE) {
                        $convertedOptions[BundleProvider::QTY_REQUEST_CODE][$optionId] = $qty;
                    }
                    break;
                }
            }
        }

        return $convertedOptions;
    }

    private function parseOption(string $option): array
    {
        $option = strtolower($option);
        if (strpos($option, ':') !== false) {
            // first symbol : read as delimiter between option and value (value can include more : in text)
            [$optionTitle, $valueTitle] = explode(':', $option, 2);
            if (preg_match('@:(\d+)$@s', $valueTitle, $matches)) {
                $valueTitle = str_replace($matches[0], '', $valueTitle);
                $qty = $matches[1];
            }
        } else {
            $optionTitle = $option;
            $valueTitle = '';
        }

        return [$optionTitle, $valueTitle, $qty ?? 1];
    }

    /**
     * @param string $typeId
     * @return ProviderInterface[]
     */
    private function getOptionProviders(string $typeId): array
    {
        switch ($typeId) {
            case Configurable::TYPE_CODE:
                $optionProviders = [
                    $this->optionProviders[ConfigurableProvider::TYPE],
                    $this->optionProviders[CustomOption::TYPE]
                ];
                break;
            case Grouped::TYPE_CODE:
                $optionProviders = [
                    $this->optionProviders[GroupedProvider::TYPE]
                ];
                break;
            case Bundle::TYPE_CODE:
                $optionProviders = [
                    $this->optionProviders[BundleProvider::TYPE],
                    $this->optionProviders[CustomOption::TYPE]
                ];
                break;
            default:
                $optionProviders = [
                    $this->optionProviders[CustomOption::TYPE]
                ];
                break;
        }

        return $optionProviders;
    }
}
