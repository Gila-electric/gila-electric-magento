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

use Webkul\RewardSystem\Api\Data\RewardcartInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Rewardcart extends AbstractModel implements RewardcartInterface, IdentityInterface
{
    public const CACHE_TAG = 'rewardsystem_rewardcart';
    /**
     * @var string
     */
    protected $_cacheTag = 'rewardsystem_rewardcart';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'rewardsystem_rewardcart';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\RewardSystem\Model\ResourceModel\Rewardcart::class);
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
     * Get Points
     *
     * @return int
     */
    public function getPoints()
    {
        return $this->getData(self::POINTS);
    }

    /**
     * Set Points
     *
     * @param int $point
     * @return int
     */
    public function setPoints($point)
    {
        return $this->setData(self::POINTS, $point);
    }

    /**
     * Get AmountFrom
     *
     * @return int|null
     */
    public function getAmountFrom()
    {
        return $this->getData(self::AMOUNT_FROM);
    }

    /**
     * Set AmountFrom
     *
     * @param float $amountFrom
     * @return int|null
     */
    public function setAmountFrom($amountFrom)
    {
        return $this->setData(self::AMOUNT_FROM, $amountFrom);
    }

    /**
     * Get AmountTo
     *
     * @return int|null
     */
    public function getAmountTo()
    {
        return $this->getData(self::AMOUNT_TO);
    }

    /**
     * Set AmountTo
     *
     * @param float $amountTo
     * @return int|null
     */
    public function setAmountTo($amountTo)
    {
        return $this->setData(self::AMOUNT_TO, $amountTo);
    }

    /**
     * Get StartDate
     *
     * @return string|null
     */
    public function getStartDate()
    {
        return $this->getData(self::START_DATE);
    }

    /**
     * Set StartDate
     *
     * @param string $startDate
     * @return string|null
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * Get EndDate
     *
     * @return string|null
     */
    public function getEndDate()
    {
        return $this->getData(self::END_DATE);
    }

    /**
     * Set EndDate
     *
     * @param string $endDate
     * @return string|null
     */
    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
    }

    /**
     * Get CreatedAt
     *
     * @return timestamp
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt
     *
     * @param date $createdAt
     * @return timestamp
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
