<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\Elasticsearch\Model\Indexer\Data\Product;

use Amasty\QuickOrder\Model\Elasticsearch\Adapter\DataMapperInterface;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper;

class ProductDataMapperPlugin
{
    /**
     * @var DataMapperInterface[]
     */
    private $dataMappers = [];

    public function __construct(array $dataMappers = [])
    {
        $this->initialize($dataMappers);
    }

    /**
     * @param array $dataMappers
     */
    public function initialize(array $dataMappers = [])
    {
        foreach ($dataMappers as $fieldCode => $dataMapper) {
            if ($dataMapper instanceof DataMapperInterface && $dataMapper->isAllowed()) {
                $this->dataMappers[$fieldCode] = $dataMapper;
            }
        }
    }

    /**
     * Prepare index data for using in search engine metadata.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ProductDataMapper $subject
     * @param callable $proceed
     * @param array $documentData
     * @param $storeId
     * @param array $context
     * @return array
     */
    public function aroundMap(
        $subject,
        callable $proceed,
        array $documentData,
        $storeId,
        $context = []
    ) {
        $documentData = $proceed($documentData, $storeId, $context);
        $this->prepareDataMappers(array_keys($documentData), (int) $storeId);
        foreach ($documentData as $productId => $document) {
            foreach ($this->dataMappers as $fieldCode => $mapper) {
                $document[$fieldCode] = $mapper->getValue($productId);
            }
            $documentData[$productId] = $document;
        }

        return $documentData;
    }

    /**
     * @param array $productIds
     * @param int $storeId
     */
    private function prepareDataMappers(array $productIds, int $storeId)
    {
        foreach ($this->dataMappers as $mapper) {
            $mapper->load($productIds, $storeId);
        }
    }
}
