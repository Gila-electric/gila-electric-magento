<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\Infortis\UltraMegamenu\Block;

use Amasty\QuickOrder\Plugin\AbstractMenuPlugin;
use Infortis\UltraMegamenu\Block\Navigation;

class NavigationPlugin extends AbstractMenuPlugin
{
    /**
     * @phpstan-ignore-next-line
     *
     * @param Navigation $subject
     * @param string $html
     * @return string
     */
    public function afterGetMegamenuHtml(Navigation $subject, string $html)
    {
        if ($this->isShowLink()) {
            $html .= $this->getNodeHtml();
        }

        return $html;
    }
}
