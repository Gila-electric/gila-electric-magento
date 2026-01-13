<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Export\Resources\Bundle;

use Amasty\QuickOrder\Api\Export\ResourceInterface;
use Amasty\QuickOrder\Model\ImportExport\Resources\Bundle\GetSelections as LoadSelections;

class GetSelections implements ResourceInterface
{
    /**
     * @var LoadSelections
     */
    private $loadSelections;

    public function __construct(LoadSelections $loadSelections)
    {
        $this->loadSelections = $loadSelections;
    }

    public function execute(array $skuArray = []): array
    {
        return $this->loadSelections->execute($skuArray, [
            'cpbs.selection_id',
            $this->loadSelections->getConnection()->getIfNullSql(
                'cpev_current.value',
                'cpev_default.value'
            )
        ]);
    }
}
