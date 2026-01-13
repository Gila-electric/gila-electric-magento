<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\ImportExport\Resources;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractResource
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    protected $columnsToSelect = [];

    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        LoggerInterface $logger
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        $this->logger = $logger;
    }

    abstract public function execute(array $skuArray = [], array $columnsToSelect = []): array;

    /**
     * @return AdapterInterface
     */
    public function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection('default');
    }

    /**
     * @param string $tableName
     * @return string
     */
    public function getTable(string $tableName): string
    {
        return $this->resourceConnection->getTableName($tableName);
    }

    /**
     * @return AttributeRepositoryInterface
     */
    public function getAttributeRepository(): AttributeRepositoryInterface
    {
        return $this->attributeRepository;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return int
     */
    public function getCurrentStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }

    /**
     * @return string
     */
    protected function getLinkField(): string
    {
        $productMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        return $productMetadata->getLinkField();
    }

    protected function getColumnsToSelect(array $columnsToSelect): array
    {
        return $columnsToSelect ?: $this->columnsToSelect;
    }
}
