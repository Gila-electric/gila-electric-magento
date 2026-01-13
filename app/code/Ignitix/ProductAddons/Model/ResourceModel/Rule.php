<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Rule extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('ignitix_productaddons_rule', 'rule_id');
    }
}