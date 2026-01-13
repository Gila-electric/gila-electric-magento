<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Observer;

use Amasty\CompanyAccount\Model\Company\IsPaymentActiveForCurrentUser;
use Amasty\CompanyAccount\Model\CompanyContext;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PaymentMethodIsActive implements ObserverInterface
{
    public const IS_AVAILABLE = 'is_available';

    /**
     * @var IsPaymentActiveForCurrentUser
     */
    private $isPaymentActive;

    /**
     * @var CompanyContext
     */
    private $companyContext;

    public function __construct(IsPaymentActiveForCurrentUser $isPaymentActive, CompanyContext $companyContext)
    {
        $this->isPaymentActive = $isPaymentActive;
        $this->companyContext = $companyContext;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $methodInstance = $observer->getMethodInstance();
        /** @var DataObject $resultObject */
        $resultObject = $observer->getResult();

        if ($resultObject->getData(self::IS_AVAILABLE)
            && $this->companyContext->getCurrentCompany()->getCompanyId()
        ) {
            $isAvailable = $this->isPaymentActive->execute($methodInstance->getCode());

            $resultObject->setData(
                self::IS_AVAILABLE,
                $isAvailable
            );
        }
    }
}
