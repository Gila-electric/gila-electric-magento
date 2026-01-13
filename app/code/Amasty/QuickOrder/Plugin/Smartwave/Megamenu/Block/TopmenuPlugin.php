<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\Smartwave\Megamenu\Block;

use Amasty\QuickOrder\Plugin\AbstractMenuPlugin;
use Smartwave\Megamenu\Block\Topmenu;

class TopmenuPlugin extends AbstractMenuPlugin
{
    /**
     * @phpstan-ignore-next-line
     *
     * @param Topmenu $subject
     * @param string $html
     * @return string
     */
    public function afterRenderCategoriesMenuHtml(Topmenu $subject, string $html)
    {
        if ($this->isShowLink()) {
            $html .= $this->getNodeHtml();
        }

        return $html;
    }
}
