<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RewardSystem\Api;

/**
 * Coupon CRUD interface
 *
 * @api
 */
interface RewardRepositoryInterface
{
    /**
     * Save credit.
     *
     * @param \Magento\SalesRule\Api\Data\CouponInterface $rewardData
     * @return \Magento\SalesRule\Api\Data\CouponInterface
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     * @throws \Magento\Framework\Exception\NoSuchEntityException If a coupon ID is sent but the coupon does not exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save($rewardData);
}
