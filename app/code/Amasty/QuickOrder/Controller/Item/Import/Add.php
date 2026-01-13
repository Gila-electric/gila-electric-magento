<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Item\Import;

use Magento\Framework\Exception\LocalizedException;

class Add extends AbstractAction
{
    public const INPUT_NAME = 'item_data';

    /**
     * @return array
     * @throws LocalizedException
     */
    public function importAction(): array
    {
        if ($itemData = $this->getItemData()) {
            return $this->getItemManager()->addItem($itemData);
        } else {
            throw new LocalizedException(__('Item data not provided.'));
        }
    }

    /**
     * @return array
     */
    private function getItemData(): array
    {
        return (array) $this->getRequest()->getParam(static::INPUT_NAME, []);
    }

    /**
     * @return int
     */
    public function calculateTotalQty(): int
    {
        return 1;
    }
}
