<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Block\Order;

class Info extends \Magento\Sales\Block\Order\Info
{
    public function getCustomerName(): string
    {
        return $this->getOrderInfo()->getCustomerName($this->getOrder());
    }

    private function getOrderInfo(): \Amasty\CompanyAccount\ViewModel\Order
    {
        return $this->getData('orderInfo');
    }
}
