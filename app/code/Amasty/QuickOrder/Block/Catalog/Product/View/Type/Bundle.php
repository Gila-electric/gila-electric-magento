<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block\Catalog\Product\View\Type;

class Bundle extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
{
    /**
     * @param bool $stripSelection
     * @return array
     */
    public function getOptions($stripSelection = false)
    {
        $this->options = null; // flush options cache because block can be used for multiple different products.
        return parent::getOptions($stripSelection);
    }
}
