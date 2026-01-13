<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Company\Role\Acl;

use Amasty\CompanyAccount\Model\Company\IsPaymentActiveForCurrentUser;
use Amasty\CompanyAccount\Model\Payment\ConfigProvider;
use Magento\Payment\Gateway\ConfigInterface;

class CreditPaymentStrategy implements IsAclShowedStrategyInterface
{
    /**
     * @var IsPaymentActiveForCurrentUser
     */
    private $isPaymentActiveForCurrentUser;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(
        IsPaymentActiveForCurrentUser $isPaymentActiveForCurrentUser,
        ConfigInterface $config
    ) {
        $this->isPaymentActiveForCurrentUser = $isPaymentActiveForCurrentUser;
        $this->config = $config;
    }

    public function execute(): bool
    {
        return $this->isPaymentActiveForCurrentUser->execute(ConfigProvider::METHOD_NAME)
            && $this->config->getValue('active');
    }
}
