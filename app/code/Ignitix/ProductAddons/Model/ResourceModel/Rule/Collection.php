<?php
declare(strict_types=1);

namespace Ignitix\ProductAddons\Model\ResourceModel\Rule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(
            \Ignitix\ProductAddons\Model\Rule::class,
            \Ignitix\ProductAddons\Model\ResourceModel\Rule::class
        );
    }
}