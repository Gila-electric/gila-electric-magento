<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Api;

interface SearchInterface
{
    public const CONTAINER_NAME = 'quickorder_search_container';

    /**
     * @param string $searchTerm
     * @return \Amasty\QuickOrder\Api\Search\ProductInterface[]
     */
    public function search(string $searchTerm);
}
