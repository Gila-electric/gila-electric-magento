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

use Webkul\RewardSystem\Api\Data\RewardattributeInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Rewardattribute extends AbstractModel implements RewardattributeInterface, IdentityInterface
{
    /**
     * @var string
     */
    public const CACHE_TAG = 'rewardsystem_rewardattribute';
    /**
     * @var string
     */
    protected $_cacheTag = 'rewardsystem_rewardattribute';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'rewardsystem_rewardattribute';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\RewardSystem\Model\ResourceModel\Rewardattribute::class);
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
     * Set entity id
     *
     * @param int $id
     * @return int
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
     * Get Attribute Code
     *
     * @return int
     */
    public function getAttributeCode()
    {
        return $this->getData(self::ATTRIBUTE_CODE);
    }

    /**
     * Set Attribute Code
     *
     * @param string $attributeCode
     * @return string
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->setData(self::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * Get Option Id
     *
     * @return int
     */
    public function getOptionId()
    {
        return $this->getData(self::OPTION_ID);
    }

    /**
     * Set Option Id
     *
     * @param int $optionId
     * @return int
     */
    public function setOptionId($optionId)
    {
        return $this->setData(self::OPTION_ID, $optionId);
    }

    /**
     * Get Option Label
     *
     * @return string
     */
    public function getOptionLabel()
    {
        return $this->getData(self::OPTION_LABEL);
    }

    /**
     * Set Option Label
     *
     * @param string $optionLabel
     * @return string
     */
    public function setOptionLabel($optionLabel)
    {
        return $this->setData(self::OPTION_LABEL, $optionLabel);
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
