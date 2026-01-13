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

interface RewardcategorySpecificInterface
{
    public const ENTITY_ID   = 'entity_id';
    public const CATEGORY_ID = 'category_id';
    public const POINTS      = 'points';
    public const START_TIME  = 'start_time';
    public const END_TIME    = 'end_time';
    public const STATUS      = 'status';

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
     * Get StartTime
     *
     * @return string|null
     */
    public function getStartTime();

    /**
     * Get EndTime
     *
     * @return string|null
     */
    public function getEndTime();

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
     * @return float|null
     */
    public function setPoints($point);

    /**
     * Set StartTime
     *
     * @param string $startTime
     * @return string|null
     */
    public function setStartTime($startTime);

    /**
     * Set EndTime
     *
     * @param string $endTime
     * @return string|null
     */
    public function setEndTime($endTime);

    /**
     * Set Remaining Reward Total
     *
     * @param int $status
     * @return int|null
     */
    public function setStatus($status);
}
