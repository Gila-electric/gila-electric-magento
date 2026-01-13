<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Model;

use Magento\Framework\Model\AbstractModel;

class Rule extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(\Ignitix\ProductAddons\Model\ResourceModel\Rule::class);
    }
}