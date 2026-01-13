<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Cart;

use Amasty\QuickOrder\Model\Cart\Result as CartResult;

interface AddProductsInterface
{
    public function execute(): CartResult;
}
