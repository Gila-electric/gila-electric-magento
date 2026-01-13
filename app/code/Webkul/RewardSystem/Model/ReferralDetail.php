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

namespace Webkul\RewardSystem\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Webkul\RewardSystem\Api\Data\ReferralDetailInterface;
use Magento\Framework\Model\AbstractModel;

class ReferralDetail extends AbstractModel implements IdentityInterface, ReferralDetailInterface
{

    public const CACHE_TAG = 'webkul_rewardsystem_referraldetail';

    /**
     * @var string
     */
    protected $_cacheTag = 'webkul_rewardsystem_referraldetail';

    /**
     * @var string
     */
    protected $_eventPrefix = 'webkul_rewardsystem_referraldetail';

    /**
     * Set resource model
     */
    public function _construct()
    {
        $this->_init(\Webkul\RewardSystem\Model\ResourceModel\ReferralDetail::class);
    }

    /**
     * Get identities.
     *
     * @return []
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return Webkul\RewardSystem\Model\ReferralDetailInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get EntityId
     *
     * @return int
     */
    public function getEntityId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set CustomerId
     *
     * @param int $customerId
     * @return Webkul\RewardSystem\Model\ReferralDetailInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get CustomerId
     *
     * @return int
     */
    public function getCustomerId()
    {
        return parent::getData(self::CUSTOMER_ID);
    }

    /**
     * Set RefereeId
     *
     * @param int $refereeId
     * @return Webkul\RewardSystem\Model\ReferralDetailInterface
     */
    public function setRefereeId($refereeId)
    {
        return $this->setData(self::REFEREE_ID, $refereeId);
    }

    /**
     * Get RefereeId
     *
     * @return int
     */
    public function getRefereeId()
    {
        return parent::getData(self::REFEREE_ID);
    }

    /**
     * Set CustomerRewardPoint
     *
     * @param float $customerRewardPoint
     * @return Webkul\RewardSystem\Model\ReferralDetailInterface
     */
    public function setCustomerRewardPoint($customerRewardPoint)
    {
        return $this->setData(self::CUSTOMER_REWARD_POINT, $customerRewardPoint);
    }

    /**
     * Get CustomerRewardPoint
     *
     * @return float
     */
    public function getCustomerRewardPoint()
    {
        return parent::getData(self::CUSTOMER_REWARD_POINT);
    }

    /**
     * Set RefereeRewardPoint
     *
     * @param float $refereeRewardPoint
     * @return Webkul\RewardSystem\Model\ReferralDetailInterface
     */
    public function setRefereeRewardPoint($refereeRewardPoint)
    {
        return $this->setData(self::REFEREE_REWARD_POINT, $refereeRewardPoint);
    }

    /**
     * Get RefereeRewardPoint
     *
     * @return float
     */
    public function getRefereeRewardPoint()
    {
        return parent::getData(self::REFEREE_REWARD_POINT);
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return Webkul\RewardSystem\Model\ReferralDetailInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return Webkul\RewardSystem\Model\ReferralDetailInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get CreatedAt
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }
}
