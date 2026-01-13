<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Item\Validation;

class Result
{
    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var string
     */
    private $message;

    /**
     * @var int|null
     */
    private $productId;

    /**
     * @var array
     */
    private $productData;

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int|null $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    public function getProductData(): array
    {
        return $this->productData ?: [];
    }

    public function setProductData(array $productData): Result
    {
        $this->productData = $productData;
        return $this;
    }
}
