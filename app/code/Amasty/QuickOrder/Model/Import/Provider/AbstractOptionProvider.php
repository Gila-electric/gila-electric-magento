<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Import\Provider;

use Amasty\QuickOrder\Api\Import\ProviderInterface;
use Amasty\QuickOrder\Api\Import\ResourceInterface;

abstract class AbstractOptionProvider implements ProviderInterface
{
    /**
     * @var ResourceInterface|null
     */
    private $optionResource;

    /**
     * @var ResourceInterface|null
     */
    private $valueResource;

    /**
     * @var array
     */
    private $optionCache;

    /**
     * @var array
     */
    private $valueCache;

    public function __construct(?ResourceInterface $optionResource = null, ?ResourceInterface $valueResource = null)
    {
        $this->optionResource = $optionResource;
        $this->valueResource = $valueResource;
    }

    /**
     * @param array $skuArray
     */
    public function initData(array $skuArray)
    {
        if ($this->optionResource) {
            $this->optionCache = $this->optionResource->execute($skuArray);
        }
        if ($this->valueResource) {
            $this->valueCache = $this->valueResource->execute($skuArray);
        }
    }

    /**
     * @return string
     */
    public function getCode()
    {
        // @phpstan-ignore-next-line
        return static::REQUEST_CODE;
    }

    /**
     * @return array
     * @throws \RuntimeException
     */
    public function getValueCache(): array
    {
        if ($this->valueCache === null) {
            $this->throwDataException();
        }
        return $this->valueCache;
    }

    /**
     * @return array
     * @throws \RuntimeException
     */
    public function getOptionCache(): array
    {
        if ($this->optionCache === null) {
            $this->throwDataException();
        }
        return $this->optionCache;
    }

    /**
     * @throws \RuntimeException
     */
    private function throwDataException()
    {
        throw new \RuntimeException('Need initialize cache for: ' . static::class);
    }
}
