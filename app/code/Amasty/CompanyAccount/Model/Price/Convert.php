<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Price;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Convert
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    public function execute(float $amount, ?string $fromCurrency, ?string $toCurrency): float
    {
        return (float) $this->priceCurrency->getCurrency(null, $fromCurrency)->convert(
            $amount,
            $toCurrency
        );
    }
}
