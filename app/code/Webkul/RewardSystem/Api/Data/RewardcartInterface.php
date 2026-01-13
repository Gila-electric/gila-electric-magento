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

interface RewardcartInterface
{
    public const ENTITY_ID      = 'entity_id';
    public const POINTS         = 'points';
    public const AMOUNT_FROM    = 'amount_from';
    public const AMOUNT_TO      = 'amount_to';
    public const START_DATE     = 'start_date';
    public const END_DATE       = 'end_date';
    public const CREATED_AT     = 'created_at';
    public const STATUS         = 'status';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get Points
     *
     * @return int|null
     */
    public function getPoints();

    /**
     * Get AmountFrom
     *
     * @return int|null
     */
    public function getAmountFrom();

    /**
     * Get AmountTo
     *
     * @return int|null
     */
    public function getAmountTo();

    /**
     * Get StartDate
     *
     * @return string|null
     */
    public function getStartDate();

    /**
     * Get EndDate
     *
     * @return string|null
     */
    public function getEndDate();

    /**
     * Get CreatedAt
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set ID
     *
     * @param int $id
     */
    public function setEntityId($id);

    /**
     * Set Points
     *
     * @param float $points
     * @return int|null
     */
    public function setPoints($points);

    /**
     * Set AmountFrom
     *
     * @param float $amountFrom
     * @return int|null
     */
    public function setAmountFrom($amountFrom);

    /**
     * Set AmountTo
     *
     * @param float $amountTo
     * @return int|null
     */
    public function setAmountTo($amountTo);

    /**
     * Set StartDate
     *
     * @param string $startDate
     * @return string|null
     */
    public function setStartDate($startDate);

    /**
     * Set EndDate
     *
     * @param string $endDate
     * @return string|null
     */
    public function setEndDate($endDate);

    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return string|null
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Status
     *
     * @param int $status
     * @return int|null
     */
    public function setStatus($status);
}
