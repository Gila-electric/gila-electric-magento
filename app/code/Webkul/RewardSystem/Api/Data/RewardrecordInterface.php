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

interface RewardrecordInterface
{
    public const ENTITY_ID                 = 'entity_id';
    public const CUSTOMER_ID               = 'customer_id';
    public const TOTAL_REWARD_POINT        = 'total_reward_point';
    public const REMAINING_REWARD_POINT    = 'remaining_reward_point';
    public const USED_REWARD_POINT         = 'used_reward_point';
    public const UPDATED_AT                = 'updated_at';

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
    public function getCustomerId();

    /**
     * Get Quote ID
     *
     * @return int|null
     */
    public function getTotalRewardPoint();

    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getRemainingRewardPoint();

    /**
     * Get Customer ID
     *
     * @return int|null
     */
    public function getUsedRewardPoint();

    /**
     * Get allowed order
     *
     * @return int|null
     */
    public function getUpdatedAt();

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
     * @param int $customerId
     * @return int|null
     */
    public function setCustomerId($customerId);

    /**
     * Set Total Reward Point
     *
     * @param float $point
     * @return float|null
     */
    public function setTotalRewardPoint($point);

    /**
     * Set Remaining Reward Total
     *
     * @param string $point
     * @return string|null
     */
    public function setRemainingRewardPoint($point);

    /**
     * Set Used Reward Point
     *
     * @param float $point
     * @return float|null
     */
    public function setUsedRewardPoint($point);

    /**
     * Set Updated At
     *
     * @param string $date
     * @return string|null
     */
    public function setUpdatedAt($date);
}
