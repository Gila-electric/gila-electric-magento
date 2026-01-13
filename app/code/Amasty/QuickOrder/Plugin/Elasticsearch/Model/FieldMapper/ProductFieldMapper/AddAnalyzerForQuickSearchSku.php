<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\Elasticsearch\Model\FieldMapper\ProductFieldMapper;

use Amasty\QuickOrder\Model\Elasticsearch\Structure\AddQuickSearchFieldMapping;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\ProductFieldMapperProxy;

class AddAnalyzerForQuickSearchSku
{
    /**
     * @var AddQuickSearchFieldMapping
     */
    private $addQuickSearchFieldMapping;

    public function __construct(
        AddQuickSearchFieldMapping $addQuickSearchFieldMapping
    ) {
        $this->addQuickSearchFieldMapping = $addQuickSearchFieldMapping;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param ProductFieldMapperProxy $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllAttributesTypes(ProductFieldMapperProxy $subject, array $result): array
    {
        return $this->addQuickSearchFieldMapping->execute($result);
    }
}
