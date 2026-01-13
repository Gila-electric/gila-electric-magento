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

use Webkul\RewardSystem\Api\Data\RewarddetailInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Rewarddetail extends AbstractModel implements RewarddetailInterface, IdentityInterface
{
    public const CACHE_TAG = 'rewardsystem_rewarddetail';
    /**
     * @var string
     */
    protected $_cacheTag = 'rewardsystem_rewarddetail';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'rewardsystem_rewarddetail';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\RewardSystem\Model\ResourceModel\Rewarddetail::class);
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
     * Get RewardPoint
     */
    public function getRewardPoint()
    {
        return $this->getData(self::REWARD_POINT);
    }

    /**
     * Set RewardPoint
     *
     * @param float $point
     */
    public function setRewardPoint($point)
    {
        return $this->setData(self::REWARD_POINT, $point);
    }

    /**
     * Get Amount
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * Set Amount
     *
     * @param float $amount
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
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

    /**
     * Get Action
     */
    public function getAction()
    {
        return $this->getData(self::ACTION);
    }

    /**
     * Set Action
     *
     * @param string $action
     */
    public function setAction($action)
    {
        return $this->setData(self::ACTION, $action);
    }

    /**
     * Get OrderId
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set OrderId
     *
     * @param int $orderId
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get TransactionAt
     */
    public function getTransactionAt()
    {
        return $this->getData(self::TRANSACTION_AT);
    }

    /**
     * Set TransactionAt
     *
     * @param string $date
     */
    public function setTransactionAt($date)
    {
        return $this->setData(self::TRANSACTION_AT, $date);
    }

    /**
     * Get Currency Code
     */
    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }

    /**
     * Set CurrencyCode
     *
     * @param string $code
     */
    public function setCurrencyCode($code)
    {
        return $this->setData(self::CURRENCY_CODE, $code);
    }

    /**
     * Get Currency Amount
     */
    public function getCurrAmount()
    {
        return $this->getData(self::CURR_AMOUNT);
    }

    /**
     * Set Currency Amount
     *
     * @param float $amount
     */
    public function setCurrAmount($amount)
    {
        return $this->setData(self::CURR_AMOUNT, $amount);
    }
 
    /**
     * Get Transaction Note
     */
    public function getTransactionNote()
    {
        return $this->getData(self::TRANSACTION_NOTE);
    }

    /**
     * Set Transaction Note
     *
     * @param string $note
     */
    public function setTransactionNote($note)
    {
        return $this->setData(self::TRANSACTION_NOTE, $note);
    }

    /**
     * Get Review Id
     */
    public function getReviewId()
    {
        return $this->getData(self::REVIEW_ID);
    }

    /**
     * Set Review Id
     *
     * @param int $reviewId
     */
    public function setReviewId($reviewId)
    {
        return $this->setData(self::REVIEW_ID, $reviewId);
    }

    /**
     * Get Is Revert
     */
    public function getIsRevert()
    {
        return $this->getData(self::IS_REVERT);
    }

    /**
     * Set Is Revert
     *
     * @param int $isRevert
     */
    public function setIsRevert($isRevert)
    {
        return $this->setData(self::IS_REVERT, $isRevert);
    }

    /**
     * Get Reward Used
     */
    public function getRewardUsed()
    {
        return $this->getData(self::REWARD_USED);
    }

    /**
     * Set Reward Used
     *
     * @param float $rewardUsed
     */
    public function setRewardUsed($rewardUsed)
    {
        return $this->setData(self::REWARD_USED, $rewardUsed);
    }

    /**
     * Get Is Expired
     */
    public function getIsExpired()
    {
        return $this->getData(self::IS_EXPIRED);
    }

    /**
     * Set Is Expired
     *
     * @param int $isExpired
     */
    public function setIsExpired($isExpired)
    {
        return $this->setData(self::IS_EXPIRED, $isExpired);
    }

    /**
     * Get Is Expiration Email Sent
     */
    public function getIsExpirationEmailSent()
    {
        return $this->getData(self::IS_EXPIRATION_EMAIL_SENT);
    }

    /**
     * Set Is Expiration Email Sent
     *
     * @param int $isExpiredEmailSent
     */
    public function setIsExpirationEmailSent($isExpiredEmailSent)
    {
        return $this->setData(self::IS_EXPIRATION_EMAIL_SENT, $isExpiredEmailSent);
    }

    /**
     * Get Expires At
     */
    public function getExpiresAt()
    {
        return $this->getData(self::EXPIRES_AT);
    }

    /**
     * Set Expires At
     *
     * @param string $expiresAt
     */
    public function setExpiresAt($expiresAt)
    {
        return $this->setData(self::EXPIRES_AT, $expiresAt);
    }
}
