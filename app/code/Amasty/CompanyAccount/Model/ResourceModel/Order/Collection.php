<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\ResourceModel\Order;

use Amasty\CompanyAccount\Api\Data\OrderInterface;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    /**
     * @param int $companyId
     * @return $this
     */
    public function getCompanyOrders(int $companyId)
    {
        $select = $this->getSelect();
        $select->joinInner(
            ['order' => $this->getTable(OrderInterface::TABLE_NAME)],
            'order.company_order_id = main_table.entity_id',
            [OrderInterface::COMPANY_ID, OrderInterface::COMPANY_NAME]
        )->where('order.company_id = ?', $companyId);

        return $this;
    }
}
