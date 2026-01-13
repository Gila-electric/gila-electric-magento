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

namespace Webkul\RewardSystem\Model;

use Webkul\RewardSystem\Api\Data\RewardrecordInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Rewardrecord extends AbstractModel implements RewardrecordInterface, IdentityInterface
{
    public const CACHE_TAG = 'rewardsystem_rewardrecord';
    /**
     * @var string
     */
    protected $_cacheTag = 'rewardsystem_rewardrecord';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'rewardsystem_rewardrecord';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\RewardSystem\Model\ResourceModel\Rewardrecord::class);
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set EntityId
     *
     * @param int $id
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get CustomerId
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set CustomerId
     *
     * @param int $customerId
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get Total Reward Point
     */
    public function getTotalRewardPoint()
    {
        return $this->getData(self::TOTAL_REWARD_POINT);
    }

    /**
     * Set Total Reward Point
     *
     * @param int $point
     */
    public function setTotalRewardPoint($point)
    {
        return $this->setData(self::TOTAL_REWARD_POINT, $point);
    }

    /**
     * Get Remaining Reward Point
     *
     * @return int
     */
    public function getRemainingRewardPoint()
    {
        return $this->getData(self::REMAINING_REWARD_POINT);
    }

    /**
     * Set Remaining Reward Point
     *
     * @param int $point
     */
    public function setRemainingRewardPoint($point)
    {
        return $this->setData(self::REMAINING_REWARD_POINT, $point);
    }

    /**
     * Get Used Reward Point
     *
     * @return int
     */
    public function getUsedRewardPoint()
    {
        return $this->getData(self::USED_REWARD_POINT);
    }

    /**
     * Set Used Reward Point
     *
     * @param int $point
     */
    public function setUsedRewardPoint($point)
    {
        return $this->setData(self::USED_REWARD_POINT, $point);
    }

    /**
     * Get UpdatedAt
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set UpdatedAt
     *
     * @param string $date
     */
    public function setUpdatedAt($date)
    {
        return $this->setData(self::UPDATED_AT, $date);
    }
}
