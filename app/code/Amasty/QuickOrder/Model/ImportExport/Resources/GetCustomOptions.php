<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\ImportExport\Resources;

use Magento\Store\Model\Store;

class GetCustomOptions extends AbstractResource
{
    public function execute(array $skuArray = [], array $columnsToSelect = []): array
    {
        $select = $this->getConnection()->select()->from(
            ['cpo' => $this->getTable('catalog_product_option')],
            $this->getColumnsToSelect($columnsToSelect)
        )->join(
            ['cpe' => $this->getTable('catalog_product_entity')],
            sprintf('cpo.product_id = cpe.%s', $this->getLinkField()),
            []
        )->joinLeft(
            ['cpot_default' => $this->getTable('catalog_product_option_title')],
            sprintf(
                'cpo.option_id = cpot_default.option_id and cpot_default.store_id = %d',
                Store::DEFAULT_STORE_ID
            ),
            []
        )->joinLeft(
            ['cpot_current' => $this->getTable('catalog_product_option_title')],
            sprintf(
                'cpo.option_id = cpot_current.option_id and cpot_current.store_id = %d',
                $this->getCurrentStoreId()
            ),
            []
        )->where('cpe.sku in (?)', $skuArray);

        return $this->getConnection()->fetchAll($select);
    }
}
