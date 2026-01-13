<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_RewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\RewardSystem\Api\Data;

interface ReferralDetailInterface
{
    public const ENTITY_ID = 'entity_id';

    public const CUSTOMER_ID = 'customer_id';

    public const REFEREE_ID = 'referee_id';

    public const CUSTOMER_REWARD_POINT = 'customer_reward_point';

    public const REFEREE_REWARD_POINT = 'referee_reward_point';

    public const STATUS = 'status';

    public const CREATED_AT = 'created_at';

    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return Webkul\RewardSystem\Api\Data\ReferralDetailInterface
     */
    public function setEntityId($entityId);

    /**
     * Get EntityId
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set CustomerId
     *
     * @param int $customerId
     * @return Webkul\RewardSystem\Api\Data\ReferralDetailInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get CustomerId
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set RefereeId
     *
     * @param int $refereeId
     * @return Webkul\RewardSystem\Api\Data\ReferralDetailInterface
     */
    public function setRefereeId($refereeId);

    /**
     * Get RefereeId
     *
     * @return int
     */
    public function getRefereeId();

    /**
     * Set CustomerRewardPoint
     *
     * @param float $customerRewardPoint
     * @return Webkul\RewardSystem\Api\Data\ReferralDetailInterface
     */
    public function setCustomerRewardPoint($customerRewardPoint);

    /**
     * Get CustomerRewardPoint
     *
     * @return float
     */
    public function getCustomerRewardPoint();

    /**
     * Set RefereeRewardPoint
     *
     * @param float $refereeRewardPoint
     * @return Webkul\RewardSystem\Api\Data\ReferralDetailInterface
     */
    public function setRefereeRewardPoint($refereeRewardPoint);

    /**
     * Get RefereeRewardPoint
     *
     * @return float
     */
    public function getRefereeRewardPoint();

    /**
     * Set Status
     *
     * @param int $status
     * @return Webkul\RewardSystem\Api\Data\ReferralDetailInterface
     */
    public function setStatus($status);

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return Webkul\RewardSystem\Api\Data\ReferralDetailInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get CreatedAt
     *
     * @return string
     */
    public function getCreatedAt();
}
