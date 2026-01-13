<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Plugin\Amazon\Payment\Gateway\Config;

use Amasty\CompanyAccount\Model\Company\IsPaymentActiveForCurrentUser;
use Amazon\Payment\Gateway\Config\Config;

class ConfigPlugin
{
    public const CODE = 'amazon_payment';

    /**
     * @var IsPaymentActiveForCurrentUser
     */
    private $isPaymentActiveForCurrentUser;

    public function __construct(IsPaymentActiveForCurrentUser $isPaymentActiveForCurrentUser)
    {
        $this->isPaymentActiveForCurrentUser = $isPaymentActiveForCurrentUser;
    }

    /**
     * @param Config $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsActive(Config $subject, $result): bool
    {
        return $result && $this->isPaymentActiveForCurrentUser->execute(self::CODE);
    }
}
