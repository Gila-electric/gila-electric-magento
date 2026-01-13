<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Cart;

class Result
{
    /**
     * Successfully added products.
     *
     * @var array
     */
    private $products = [];

    /**
     * Products cant'be added.
     *
     * @var array
     */
    private $errors = [];

    public function addProduct(int $productId): void
    {
        $this->products[] = $productId;
    }

    public function getAddedProducts(): array
    {
        return $this->products;
    }

    public function getCountAddedProducts(): int
    {
        return count($this->products);
    }

    public function addError(array $error): void
    {
        $this->errors[] = $error;
    }

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
