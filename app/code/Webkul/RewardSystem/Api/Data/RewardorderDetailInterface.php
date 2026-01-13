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

interface RewardorderDetailInterface
{
    public const ENTITY_ID  = 'entity_id';
    public const ORDER_ID   = 'order_id';
    public const ITEM_ID    = 'item_id';
    public const POINTS     = 'points';
    public const QTY        = 'qty';
    public const IS_QTY_WISE = 'is_qty_wise';

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getEntityId();
    /**
     * Set Entity Id
     *
     * @param int $id
     */
    public function setEntityId($id);
    /**
     * Get Order Id
     *
     * @return int|null
     */
    public function getOrderId();
    /**
     * Set Order Id
     *
     * @param int $orderId
     */
    public function setOrderId($orderId);
    /**
     * Get Item Id
     *
     * @return int|null
     */
    public function getItemId();
    /**
     * Set Item Id
     *
     * @param int $itemId
     */
    public function setItemId($itemId);
    /**
     * Get Points
     *
     * @return float|null
     */
    public function getPoints();
    /**
     * Set Points
     *
     * @param float $points
     */
    public function setPoints($points);
    /**
     * Get Quantity
     *
     * @return int|null
     */
    public function getQty();
    /**
     * Set Quantity
     *
     * @param int $qty
     */
    public function setQty($qty);
    /**
     * Get quantity wise reward points is enable
     *
     * @return int|null
     */
    public function getIsQtyWise();
    /**
     * Set quantity wise reward points is enable
     *
     * @param int $isQtyWise
     */
    public function setIsQtyWise($isQtyWise);
}
