<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action;

use Amasty\QuickOrder\Model\ResourceModel\LoadSkuByIds;
use Amasty\QuickOrder\Setup\Patch\Data\AddAmastySkuAttribute;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;

class DataProviderPlugin
{
    /**
     * @var LoadSkuByIds
     */
    private $loadSkuByIds;

    public function __construct(LoadSkuByIds $loadSkuByIds)
    {
        $this->loadSkuByIds = $loadSkuByIds;
    }

    /**
     * Add SKU data for each product. Magento merge SKUs for simples as for other searchable attributes in DataProvider
     * @param DataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetProductAttributes(DataProvider $subject, $result)
    {
        if (is_array($result)) {
            $productSkuData = $this->loadSkuByIds->execute(array_keys($result));
            $quickorderSkuAttributeId = $subject
                ->getSearchableAttribute(AddAmastySkuAttribute::ATTRIBUTE_NAME)
                ->getAttributeId();

            foreach ($result as $entityId => $entityData) {
                if (isset($productSkuData[$entityId])) {
                    $result[$entityId][$quickorderSkuAttributeId] = $productSkuData[$entityId];
                }
            }
        }

        return $result;
    }
}
