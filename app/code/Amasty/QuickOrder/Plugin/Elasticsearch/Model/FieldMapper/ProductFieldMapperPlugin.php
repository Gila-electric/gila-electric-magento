<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\Elasticsearch\Model\FieldMapper;

use Amasty\QuickOrder\Plugin\Elasticsearch\AdditionalFieldMapper;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\ProductFieldMapper;

class ProductFieldMapperPlugin extends AdditionalFieldMapper
{
    /**
     * @param ProductFieldMapper $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllAttributesTypes($subject, array $result): array
    {
        return $this->updateFields($result);
    }
}
