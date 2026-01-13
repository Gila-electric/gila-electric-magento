<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\Elasticsearch\Model\Indexer\Data\Product\ProductDataMapper;

use Amasty\ElasticSearch\Model\Indexer\Data\Product\ProductDataMapper;
use Amasty\QuickOrder\Setup\Patch\Data\AddAmastySkuAttribute;
use Magento\Eav\Model\Entity\Attribute;

class DisableExcludeQuickOrderSkuFromMerge
{
    /**
     * @phpstan-ignore-next-line
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ProductDataMapper $productDataMapper
     * @param bool $result
     * @param Attribute $attribute
     * @return bool
     */
    public function afterIsAttributeExcludedFromMerge(
        ProductDataMapper $productDataMapper,
        bool $result,
        Attribute $attribute
    ): bool {
        return $attribute->getAttributeCode() === AddAmastySkuAttribute::ATTRIBUTE_NAME ? false : $result;
    }
}
