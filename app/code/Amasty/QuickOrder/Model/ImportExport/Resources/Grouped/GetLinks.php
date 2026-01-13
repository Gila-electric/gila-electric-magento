<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\ImportExport\Resources\Grouped;

use Amasty\QuickOrder\Model\ImportExport\Resources\AbstractResource;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;

class GetLinks extends AbstractResource
{
    public function execute(array $skuArray = [], array $columnsToSelect = []): array
    {
        try {
            $linkField = $this->getLinkField();

            $productNameAttributeId = $this->getAttributeRepository()
                ->get(Product::ENTITY, 'name')
                ->getAttributeId();

            $select = $this->getConnection()->select()->from(
                ['cpl' => $this->getTable('catalog_product_link')],
                $this->getColumnsToSelect($columnsToSelect)
            )->join(
                ['cpe_parent' => $this->getTable('catalog_product_entity')],
                sprintf('cpe_parent.%s = cpl.product_id', $this->getLinkField()),
                []
            )->join(
                ['cpe_child' => $this->getTable('catalog_product_entity')],
                'cpe_child.entity_id = cpl.linked_product_id',
                []
            )->joinLeft(
                ['cpev_default' => $this->getTable('catalog_product_entity_varchar')],
                sprintf(
                    'cpev_default.%1$s=cpe_child.%1$s and cpev_default.attribute_id=%2$d
                    and cpev_default.store_id=%3$d',
                    $linkField,
                    $productNameAttributeId,
                    Store::DEFAULT_STORE_ID
                ),
                []
            )->joinLeft(
                ['cpev_current' => $this->getTable('catalog_product_entity_varchar')],
                sprintf(
                    'cpev_current.%1$s=cpe_child.%1$s and cpev_current.attribute_id=%2$d
                    and cpev_current.store_id=%3$d',
                    $linkField,
                    $productNameAttributeId,
                    $this->getCurrentStoreId()
                ),
                []
            )->where('link_type_id = 3 and cpe_parent.sku in (?)', $skuArray);

            $result = $this->getConnection()->fetchPairs($select);
        } catch (NoSuchEntityException $e) {
            $result = [];
            $this->getLogger()->error($e->getMessage());
        }

        return $result;
    }
}
