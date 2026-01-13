<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Elasticsearch\Adapter\DataMapper;

use Amasty\QuickOrder\Model\Elasticsearch\Adapter\DataMapperInterface;
use Amasty\QuickOrder\Model\ResourceModel\Inventory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class StockStatus implements DataMapperInterface
{
    public const IN_STOCK = 1;

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Inventory $inventory,
        StoreManagerInterface $storeManager
    ) {
        $this->inventory = $inventory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $entityId
     * @return int
     */
    public function getValue($entityId): int
    {
        return (int)($this->data[$entityId] ?? self::IN_STOCK);
    }

    /**
     * @inheritDoc
     */
    public function isAllowed()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function load(array $productIds, int $storeId)
    {
        if ($productIds) {
            try {
                $this->data = $this->inventory->getStockInfo(
                    $productIds,
                    'entity_id',
                    $this->storeManager->getStore($storeId)->getWebsite()->getCode()
                );
            } catch (NoSuchEntityException $e) {
                $this->data = [];
            }
        }
    }
}
