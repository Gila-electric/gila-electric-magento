<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Controller\Item\Move;

class InQuote extends AbstractAction
{
    /**
     * @return string
     */
    public function getRedirectAction(): string
    {
        return 'amasty_quote/cart';
    }
}
