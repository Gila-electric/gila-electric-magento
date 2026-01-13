<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\ImportExport\Resources;

class GetProductTypes extends AbstractResource
{
    /**
     * @var string[]
     */
    protected $columnsToSelect = [
        'sku',
        'type_id'
    ];

    public function execute(array $skuArray = [], array $columnsToSelect = []): array
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('catalog_product_entity'),
            $this->columnsToSelect
        )->where('sku IN (?)', $skuArray);

        return $this->getConnection()->fetchPairs($select);
    }
}
