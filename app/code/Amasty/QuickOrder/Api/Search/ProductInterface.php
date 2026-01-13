<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Api\Search;

interface ProductInterface
{
    public const ID = 'id';
    public const SKU = 'sku';
    public const NAME = 'name';
    public const PRICE = 'price';
    public const IMAGE = 'image';
    public const TYPE_ID = 'type_id';
    public const IMAGE_LABEL = 'image_label';

    /**
     * Product id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set product id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Product sku
     *
     * @return string
     */
    public function getSku();

    /**
     * Set product sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Product name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set product name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Product price
     *
     * @return string|null
     */
    public function getPrice();

    /**
     * Set product price
     *
     * @param string $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Product image
     *
     * @return string|null
     */
    public function getImage();

    /**
     * Set product image
     *
     * @param string $image
     * @return $this
     */
    public function setImage($image);

    /**
     * Product type
     *
     * @return string
     */
    public function getTypeId(): string;

    /**
     * Set product type
     *
     * @param string $typeId
     * @return $this
     */
    public function setTypeId(string $typeId);

    /**
     * @return string|null
     */
    public function getImageLabel(): ?string;

    /**
     * @param string $imageLabel
     * @return void
     */
    public function setImageLabel(string $imageLabel): void;
}
