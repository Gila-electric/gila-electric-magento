<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Block;

use Magento\Framework\View\Element\Template;

class Popup extends Template
{
    public function getJsLayout()
    {
        return json_encode($this->jsLayout, JSON_HEX_TAG);
    }
}
