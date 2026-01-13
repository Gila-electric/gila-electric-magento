<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_RewardSystem
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\RewardSystem\Api\Data;

interface RewardattributeInterface
{
    public const ENTITY_ID      = 'entity_id';
    public const POINTS         = 'points';
    public const ATTRIBUTE_CODE = 'attribute_code';
    public const OPTION_ID      = 'option_id';
    public const OPTION_LABEL   = 'option_label';
    public const CREATED_AT     = 'created_at';
    public const STATUS         = 'status';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get Points
     *
     * @return int|null
     */
    public function getPoints();

    /**
     * Get AttributeCode
     *
     * @return string|null
     */
    public function getAttributeCode();

    /**
     * Get OptionId
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Get OptionLabel
     *
     * @return string|null
     */
    public function getOptionLabel();

    /**
     * Get CreatedAt
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set ID
     *
     * @param int $id
     * @return int
     */
    public function setEntityId($id);

    /**
     * Set Points
     *
     * @param float $points
     * @return int|null
     */
    public function setPoints($points);

    /**
     * Set AttributeCode
     *
     * @param string $attributeCode
     * @return string|null
     */
    public function setAttributeCode($attributeCode);

    /**
     * Set OptionId
     *
     * @param int $optionId
     * @return int|null
     */
    public function setOptionId($optionId);

    /**
     * Set OptionLabel
     *
     * @param string $optionLabel
     * @return string|null
     */
    public function setOptionLabel($optionLabel);

    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return string|null
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Status
     *
     * @param int $status
     * @return int|null
     */
    public function setStatus($status);
}
