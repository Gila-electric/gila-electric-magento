<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\ImportExport\Resources\Configurable;

use Amasty\QuickOrder\Model\ImportExport\Resources\AbstractResource;

class GetSuperAttributes extends AbstractResource
{
    public function execute(array $skuArray = [], array $columnsToSelect = []): array
    {
        $select = $this->getConnection()->select()->from(
            ['ea' => $this->getTable('eav_attribute')],
            $this->getColumnsToSelect($columnsToSelect)
        )->join(
            ['cpsa' => $this->getTable('catalog_product_super_attribute')],
            'ea.attribute_id = cpsa.attribute_id',
            []
        )->joinLeft(
            ['eal' => $this->getTable('eav_attribute_label')],
            sprintf('ea.attribute_id = eal.attribute_id and store_id = %d', $this->getCurrentStoreId()),
            []
        )->group('ea.attribute_id');

        return $this->getConnection()->fetchPairs($select);
    }
}
