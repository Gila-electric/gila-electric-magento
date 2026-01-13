<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Plugin\Theme\Block\Html;

use Amasty\QuickOrder\Plugin\AbstractMenuPlugin;
use Magento\Theme\Block\Html\Topmenu;

class TopmenuPlugin extends AbstractMenuPlugin
{
    /**
     * @param Topmenu $subject
     * @return null
     */
    public function beforeGetHtml(Topmenu $subject)
    {
        if ($this->isShowLink()) {
            $this->addQuickOrderNode($subject->getMenu());
        }

        return null;
    }
}
