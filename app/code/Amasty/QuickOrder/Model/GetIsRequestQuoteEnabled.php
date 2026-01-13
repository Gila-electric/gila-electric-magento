<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager as ModuleManager;
use Psr\Log\LoggerInterface;

class GetIsRequestQuoteEnabled
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConfigProvider $configProvider,
        CustomerSession $customerSession,
        ModuleManager $moduleManager,
        LoggerInterface $logger
    ) {
        $this->configProvider = $configProvider;
        $this->customerSession = $customerSession;
        $this->moduleManager = $moduleManager;
        $this->logger = $logger;
    }

    public function execute(): bool
    {
        try {
            $result = $this->moduleManager->isEnabled('Amasty_RequestQuote')
                && $this->configProvider->isRequestQuoteEnabled((int) $this->customerSession->getCustomerGroupId())
                && $this->configProvider->isRequestQuoteButtonEnabled();
        } catch (LocalizedException $e) {
            $result = false;
            $this->logger->error($e->getMessage());
        }

        return $result;
    }
}
