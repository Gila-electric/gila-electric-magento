<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\Elasticsearch\Model\FieldMapper;

use Amasty\ElasticSearch\Model\Indexer\Structure\EntityBuilder\Product as EntityBuilderProduct;
use Amasty\QuickOrder\Plugin\Elasticsearch\AdditionalFieldMapper;

class EntityBuilderProductPlugin extends AdditionalFieldMapper
{
    /**
     * @phpstan-ignore-next-line
     *
     * @param EntityBuilderProduct $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuildEntityFields(EntityBuilderProduct $subject, array $result): array
    {
        return $this->updateFields($result);
    }
}
