<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\ImportExport\Resources\Grouped;

use Amasty\QuickOrder\Model\ImportExport\Resources\AbstractResource;

class GetLinkedProducts extends AbstractResource
{
    /**
     * @var string[]
     */
    protected $columnsToSelect = [
        'parent' => 'cpe_parent.sku',
        'child' =>  'cpl.linked_product_id'
    ];

    public function execute(array $skuArray = [], array $columnsToSelect = []): array
    {
        $select = $this->getConnection()->select()->from(
            ['cpl' => $this->getTable('catalog_product_link')],
            $this->columnsToSelect
        )->join(
            ['cpe_parent' => $this->getTable('catalog_product_entity')],
            sprintf('cpe_parent.%s = cpl.product_id', $this->getLinkField()),
            []
        )->join(
            ['cpe_child' => $this->getTable('catalog_product_entity')],
            'cpe_child.entity_id = cpl.linked_product_id',
            []
        )->where('link_type_id = 3 and cpe_parent.sku in (?)', $skuArray);

        $data = [];
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $data[$row['parent']][] = $row['child'];
        }

        return $data;
    }
}
