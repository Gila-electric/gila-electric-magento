<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Search;

use Amasty\QuickOrder\Api\Search\ProductInterface;
use Magento\Framework\DataObject;

class Product extends DataObject implements ProductInterface
{
    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(ProductInterface::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        return $this->setData(ProductInterface::ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getSku()
    {
        return $this->getData(ProductInterface::SKU);
    }

    /**
     * @inheritdoc
     */
    public function setSku($sku)
    {
        return $this->setData(ProductInterface::SKU, $sku);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData(ProductInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(ProductInterface::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getPrice()
    {
        return $this->getData(ProductInterface::PRICE);
    }

    /**
     * @inheritdoc
     */
    public function setPrice($price)
    {
        return $this->setData(ProductInterface::PRICE, $price);
    }

    /**
     * @inheritdoc
     */
    public function getImage()
    {
        return $this->getData(ProductInterface::IMAGE);
    }

    /**
     * @inheritdoc
     */
    public function setImage($image)
    {
        return $this->setData(ProductInterface::IMAGE, $image);
    }

    /**
     * @inheritdoc
     */
    public function getTypeId(): string
    {
        return $this->getData(ProductInterface::TYPE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setTypeId(string $typeId)
    {
        return $this->setData(ProductInterface::TYPE_ID, $typeId);
    }

    public function getImageLabel(): ?string
    {
        return $this->getData(self::IMAGE_LABEL);
    }

    public function setImageLabel(string $imageLabel): void
    {
        $this->setData(self::IMAGE_LABEL, $imageLabel);
    }
}
