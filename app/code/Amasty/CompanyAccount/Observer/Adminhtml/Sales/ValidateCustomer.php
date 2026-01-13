<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Observer\Adminhtml\Sales;

use Amasty\CompanyAccount\Model\Company\CustomerCompanyResolver;
use Amasty\CompanyAccount\Model\Company\IsResourceAllowed;
use Amasty\CompanyAccount\Plugin\Checkout\Controller\Index\IndexPlugin;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * adminhtml observer
 * event adminhtml_sales_order_create_process_data_before
 * observer name Amasty_CompanyAccount::ValidateCustomer
 */
class ValidateCustomer implements ObserverInterface
{

    /**
     * @var CustomerCompanyResolver
     */
    private $companyResolver;

    /**
     * @var IsResourceAllowed
     */
    private $isResourceAllowed;

    public function __construct(CustomerCompanyResolver $companyResolver, IsResourceAllowed $isResourceAllowed)
    {
        $this->companyResolver = $companyResolver;
        $this->isResourceAllowed = $isResourceAllowed;
    }

    /**
     * Observer for adminhtml_sales_order_create_process_data_before
     *
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();
        /** @var RequestInterface $request */
        $request = $event->getData('request_model');
        if ($customerId = (int) $request->getParam('customer_id')) {

            $amcompanyAttributes = $this->companyResolver->resolveForCustomerId($customerId);

            if ($amcompanyAttributes
                && ($roleId = (int) $amcompanyAttributes->getRoleId())
                && !$this->isResourceAllowed->checkResourceForRole(IndexPlugin::RESOURCE, $roleId)
            ) {
                throw new LocalizedException(__('Customer is restricted to place orders by Company Account Role'));
            }
        }
    }
}
