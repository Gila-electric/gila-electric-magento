<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Module\Manager as ModuleManager;

class Inventory extends AbstractDb
{
    public const MSI_STOCK_TABLE = 'inventory_stock_%d';
    public const DEFAULT_STOCK_TABLE = 'cataloginventory_stock_status';

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var array
     */
    private $stockIds;

    /**
     * @var bool|null
     */
    private $msiEnabled;

    public function __construct(
        ModuleManager $moduleManager,
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->stockIds = [];
    }

    /**
     * @param array $values
     * @param string $field
     * @param string $websiteCode
     * @return array
     */
    public function getStockInfo(array $values, string $field, string $websiteCode): array
    {
        if ($this->isMsiEnabled()) {
            $stockId = $this->getStockId($websiteCode);
            if ($this->isMsiStockTableExists($stockId)) {
                $result = $this->getMsiStockInfo($stockId, $values, $field);
            } else {
                $result = $this->getDefaultStockInfo($values, $field);
            }
        } else {
            $result = $this->getDefaultStockInfo($values, $field);
        }

        return $result;
    }

    /**
     * For MSI.
     *
     * @param string $websiteCode
     *
     * @return int
     */
    public function getStockId(string $websiteCode): int
    {
        if (!isset($this->stockIds[$websiteCode])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_stock_sales_channel'), ['stock_id'])
                ->where('type = \'website\' AND code = ?', $websiteCode);

            $this->stockIds[$websiteCode] = (int) $this->getConnection()->fetchOne($select);
        }

        return $this->stockIds[$websiteCode];
    }

    /**
     * @param int $stockId
     * @return bool
     */
    private function isMsiStockTableExists($stockId): bool
    {
        return $this->getConnection()->isTableExists($this->getMsiStockTable($stockId));
    }

    /**
     * @param $stockId
     * @return string
     */
    private function getMsiStockTable($stockId): string
    {
        return $this->getTable(sprintf(self::MSI_STOCK_TABLE, $stockId));
    }

    /**
     * @param $stockId
     * @param array $values
     * @param string $field
     * @return array
     */
    private function getMsiStockInfo($stockId, array $values, string $field): array
    {
        $select = $this->getConnection()->select()->from(
            ['stock' => $this->getTable($this->getMsiStockTable($stockId))],
            []
        )->where(
            sprintf(
                '%s IN (?)',
                $this->getConnection()->quoteIdentifier($field)
            ),
            $values
        );
        if ($field === 'entity_id') {
            $select->join(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'stock.sku = cpe.sku',
                [$field]
            );
        } else {
            $select->columns([$field]);
        }
        $select->columns(['is_salable']);

        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * @param array $values
     * @param string $field
     * @return array
     */
    private function getDefaultStockInfo(array $values, string $field): array
    {
        if ($field === 'entity_id') {
            $field = 'product_id';
        }

        $select = $this->getConnection()->select()->from(
            ['stock' => $this->getTable(self::DEFAULT_STOCK_TABLE)],
            []
        )->where(
            sprintf(
                '%s IN (?)',
                $this->getConnection()->quoteIdentifier($field)
            ),
            $values
        );

        if ($field === 'sku') {
            $select->join(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'stock.product_id = cpe.entity_id',
                [$field]
            );
        } else {
            $select->columns(['product_id']);
        }
        $select->columns(['stock_status']);

        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * @return bool
     */
    private function isMsiEnabled(): bool
    {
        if ($this->msiEnabled === null) {
            $this->msiEnabled = $this->moduleManager->isEnabled('Magento_Inventory');
        }

        return $this->msiEnabled;
    }
}
