<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Export\Resources\Configurable;

use Amasty\QuickOrder\Api\Export\ResourceInterface;
use Amasty\QuickOrder\Model\ImportExport\Resources\Configurable\GetSuperAttributes as LoadSuperAttributes;

class GetSuperAttributes implements ResourceInterface
{
    /**
     * @var LoadSuperAttributes
     */
    private $loadSuperAttributes;

    public function __construct(LoadSuperAttributes $loadSuperAttributes)
    {
        $this->loadSuperAttributes = $loadSuperAttributes;
    }

    public function execute(array $skuArray = []): array
    {
        return $this->loadSuperAttributes->execute($skuArray, [
            'ea.attribute_id',
            $this->loadSuperAttributes->getConnection()->getIfNullSql(
                'eal.value',
                'ea.frontend_label'
            )
        ]);
    }
}
