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

interface RewarddetailInterface
{
    public const ENTITY_ID                 = 'entity_id';
    public const CUSTOMER_ID               = 'customer_id';
    public const REWARD_POINT              = 'reward_point';
    public const AMOUNT                    = 'amount';
    public const STATUS                    = 'status';
    public const ACTION                    = 'action';
    public const ORDER_ID                  = 'order_id';
    public const TRANSACTION_AT            = 'transaction_at';
    public const CURRENCY_CODE             = 'currency_code';
    public const CURR_AMOUNT               = 'curr_amount';
    public const TRANSACTION_NOTE          = 'transaction_note';
    public const REVIEW_ID                 = 'review_id';
    public const IS_REVERT                 = 'is_revert';
    public const REWARD_USED               = 'reward_used';
    public const IS_EXPIRED                = 'is_expired';
    public const IS_EXPIRATION_EMAIL_SENT  = 'is_expiration_email_sent';
    public const EXPIRES_AT                = 'expires_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get Customer ID
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Get Reward Point
     *
     * @return int|null
     */
    public function getRewardPoint();

    /**
     * Get Amount
     *
     * @return int|null
     */
    public function getAmount();

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get Action
     *
     * @return int|null
     */
    public function getAction();

    /**
     * Get Order id
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Get Tranction Date
     *
     * @return int|null
     */
    public function getTransactionAt();

    /**
     * Get Currency Code
     *
     * @return int|null
     */
    public function getCurrencyCode();

    /**
     * Get Current Amount
     *
     * @return int|null
     */
    public function getCurrAmount();

    /**
     * Get Transaction Note
     *
     * @return int|null
     */
    public function getTransactionNote();

    /**
     * Get Review Id
     *
     * @return int|null
     */
    public function getReviewId();

    /**
     * Get Is Reward Revert
     *
     * @return int|null
     */
    public function getIsRevert();

    /**
     * Get REWARD USED
     *
     * @return float|null
     */
    public function getRewardUsed();

    /**
     * Get Is Expired
     *
     * @return int|null
     */
    public function getIsExpired();

    /**
     * Get Is Expiration Email Sent
     *
     * @return int|null
     */
    public function getIsExpirationEmailSent();

    /**
     * Get Expires Date
     *
     * @return string|null
     */
    public function getExpiresAt();

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
     * Set Reward Point
     *
     * @param float $point
     * @return float|null
     */
    public function setRewardPoint($point);

    /**
     * Set Amount
     *
     * @param float $amount
     * @return float|null
     */
    public function setAmount($amount);

    /**
     * Set Status
     *
     * @param int $status
     * @return int|null
     */
    public function setStatus($status);

    /**
     * Set Action
     *
     * @param string $action
     * @return string|null
     */
    public function setAction($action);

    /**
     * Set Order Id
     *
     * @param int $orderId
     * @return int|null
     */
    public function setOrderId($orderId);

    /**
     * Set Tranction Date
     *
     * @param string $date
     * @return string|null
     */
    public function setTransactionAt($date);

    /**
     * Set Currency Code
     *
     * @param string $code
     * @return string|null
     */
    public function setCurrencyCode($code);

    /**
     * Set Current Amount
     *
     * @param float $amount
     * @return float|null
     */
    public function setCurrAmount($amount);

    /**
     * Set Transaction Note
     *
     * @param string $note
     * @return string|null
     */
    public function setTransactionNote($note);
    /**
     * Set Review Id
     *
     * @param int $reviewId
     * @return int|null
     */
    public function setReviewId($reviewId);

    /**
     * Set Is Reward Revert
     *
     * @param int $isRevert
     * @return int|null
     */
    public function setIsRevert($isRevert);

    /**
     * Set REWARD USED
     *
     * @param float $rewardUsed
     * @return float|null
     */
    public function setRewardUsed($rewardUsed);

    /**
     * Set Is Expired
     *
     * @param int $isExpired
     * @return int|null
     */
    public function setIsExpired($isExpired);

    /**
     * Set Is Expiration Email Sent
     *
     * @param int $isExpiredEmailSent
     * @return int|null
     */
    public function setIsExpirationEmailSent($isExpiredEmailSent);

    /**
     * Set Expires Date
     *
     * @param string $expiresAt
     * @return string|null
     */
    public function setExpiresAt($expiresAt);
}
