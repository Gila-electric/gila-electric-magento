<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\CustomerData;

use Amasty\QuickOrder\Model\Category\Item\Manager as ItemManager;
use Magento\Customer\CustomerData\SectionSourceInterface;

class Category implements SectionSourceInterface
{
    /**
     * @var ItemManager
     */
    private $itemManager;

    public function __construct(ItemManager $itemManager)
    {
        $this->itemManager = $itemManager;
    }

    public function getSectionData()
    {
        return $this->itemManager->getItems();
    }
}
