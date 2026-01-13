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
use Psr\Log\LoggerInterface;

class GetIsAvailable implements IsAvailableInterface
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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConfigProvider $configProvider,
        CustomerSession $customerSession,
        LoggerInterface $logger
    ) {
        $this->configProvider = $configProvider;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    public function execute(): bool
    {
        try {
            $result = $this->configProvider->isQuickOrderEnabled()
                && $this->configProvider->isCustomerGroupEnabled((int) $this->customerSession->getCustomerGroupId());
        } catch (LocalizedException $e) {
            $result = false;
            $this->logger->error($e->getMessage());
        }

        return $result;
    }
}
