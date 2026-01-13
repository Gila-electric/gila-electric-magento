<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\ImportExport\Resources\Bundle;

use Amasty\QuickOrder\Model\ImportExport\Resources\AbstractResource;
use Magento\Store\Model\Store;

class GetOptions extends AbstractResource
{
    public function execute(array $skuArray = [], array $columnsToSelect = []): array
    {
        $select = $this->getConnection()->select()->from(
            ['cpbo' => $this->getTable('catalog_product_bundle_option')],
            $this->getColumnsToSelect($columnsToSelect)
        )->joinLeft(
            ['cpbov_default' => $this->getTable('catalog_product_bundle_option_value')],
            sprintf(
                'cpbov_default.option_id = cpbo.option_id and cpbov_default.store_id = %d',
                Store::DEFAULT_STORE_ID
            ),
            []
        )->joinLeft(
            ['cpbov_current' => $this->getTable('catalog_product_bundle_option_value')],
            sprintf(
                'cpbov_current.option_id = cpbo.option_id and cpbov_default.store_id = %d',
                $this->getCurrentStoreId()
            ),
            []
        );

        return $this->getConnection()->fetchPairs($select);
    }
}
