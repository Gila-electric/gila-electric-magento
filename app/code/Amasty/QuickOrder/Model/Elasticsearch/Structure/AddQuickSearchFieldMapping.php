<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Elasticsearch\Structure;

use Amasty\QuickOrder\Plugin\Elasticsearch\Model\Adapter\Index\Builder\CreateNewAnalyzer;
use Amasty\QuickOrder\Setup\Patch\Data\AddAmastySkuAttribute;

class AddQuickSearchFieldMapping
{
    public function execute(array $fieldsMapping): array
    {
        $fieldsMapping[AddAmastySkuAttribute::ATTRIBUTE_NAME] = [
            'type' => 'text',
            'fielddata' => true,
            'analyzer' => CreateNewAnalyzer::ANALYZER_CODE
        ];

        return $fieldsMapping;
    }
}
