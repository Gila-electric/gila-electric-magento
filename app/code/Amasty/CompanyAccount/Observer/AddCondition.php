<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddCondition implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $additional = $observer->getEvent()->getAdditional();
        $conditions = $additional->getConditions() ?: [];
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'label' => __('Company'),
                    'value' => \Amasty\CompanyAccount\Model\Condition\Company::class,
                ],
            ]
        );
        $additional->setConditions($conditions);
    }
}
