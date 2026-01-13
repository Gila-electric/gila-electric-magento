<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Import\Resources\Bundle;

use Amasty\QuickOrder\Api\Import\ResourceInterface;
use Amasty\QuickOrder\Model\ImportExport\Resources\Bundle\GetOptions as LoadOptions;

class GetOptions implements ResourceInterface
{
    /**
     * @var LoadOptions
     */
    private $loadOptions;

    public function __construct(LoadOptions $loadOptions)
    {
        $this->loadOptions = $loadOptions;
    }

    public function execute(array $skuArray = []): array
    {
        return $this->loadOptions->execute($skuArray, [
            sprintf('LOWER(%s)', $this->loadOptions->getConnection()->getIfNullSql(
                'cpbov_current.title',
                'cpbov_default.title'
            )),
            'cpbo.option_id'
        ]);
    }
}
