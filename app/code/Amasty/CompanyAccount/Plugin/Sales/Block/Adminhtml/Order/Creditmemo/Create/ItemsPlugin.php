<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Plugin\Sales\Block\Adminhtml\Order\Creditmemo\Create;

use Amasty\CompanyAccount\Model\Payment\ConfigProvider as PaymentConfigProvider;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Create\Items;

class ItemsPlugin
{
    /**
     * Add company credit button.
     *
     * @param Items $subject
     * @return void
     */
    public function beforeToHtml(Items $subject)
    {
        $order = $subject->getOrder();
        if ($order->getPayment()->getMethod() === PaymentConfigProvider::METHOD_NAME) {
            $refundBtn = $subject->getChildBlock('submit_offline');
            $refundBtn->setData('class', 'save submit-button action-secondary');
        }
    }
}
