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

interface RewardcategoryInterface
{
    public const ENTITY_ID    = 'entity_id';
    public const CATEGORY_ID  = 'category_id';
    public const POINTS       = 'points';
    public const STATUS       = 'status';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get Seller ID
     *
     * @return int|null
     */
    public function getCategoryId();

    /**
     * Get Quote ID
     *
     * @return int|null
     */
    public function getPoints();

    /**
     * Get Order ID
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
     * Set Customer ID
     *
     * @param int $categoryId
     * @return int|null
     */
    public function setCategoryId($categoryId);

    /**
     * Set Total Reward Point
     *
     * @param float $point
     * @return int|null
     */
    public function setPoints($point);

    /**
     * Set Remaining Reward Total
     *
     * @param int $status
     * @return int|null
     */
    public function setStatus($status);
}
