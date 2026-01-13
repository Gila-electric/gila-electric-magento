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

use Webkul\RewardSystem\Api\Data\RewardcategorySpecificInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class RewardcategorySpecific extends AbstractModel implements RewardcategorySpecificInterface, IdentityInterface
{
    public const CACHE_TAG = 'rewardsystem_rewardcategoryspecific';
    /**
     * @var string
     */
    protected $_cacheTag = 'rewardsystem_rewardcategoryspecific';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'rewardsystem_rewardcategoryspecific';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\RewardSystem\Model\ResourceModel\RewardcategorySpecific::class);
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
     * Get Category Id
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * Set Category Id
     *
     * @param int $categoryId
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }

    /**
     * Get Points
     */
    public function getPoints()
    {
        return $this->getData(self::POINTS);
    }

     /**
      * Set Points
      *
      * @param int $point
      */
    public function setPoints($point)
    {
        return $this->setData(self::POINTS, $point);
    }

    /**
     * Get Start Time
     */
    public function getStartTime()
    {
        return $this->getData(self::START_TIME);
    }

    /**
     * Set Start Time
     *
     * @param string $startTime
     */
    public function setStartTime($startTime)
    {
        return $this->setData(self::START_TIME, $startTime);
    }

    /**
     * Get End Time
     */
    public function getEndTime()
    {
        return $this->getData(self::END_TIME);
    }

    /**
     * Set End Time
     *
     * @param string $endTime
     */
    public function setEndTime($endTime)
    {
        return $this->setData(self::END_TIME, $endTime);
    }

    /**
     * Get Status
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status
     *
     * @param int $status
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
