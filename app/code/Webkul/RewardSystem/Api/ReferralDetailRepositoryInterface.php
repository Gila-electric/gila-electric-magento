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

namespace Webkul\RewardSystem\Api;

interface ReferralDetailRepositoryInterface
{
    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\RewardSystem\Model\ReferralDetail
     */
    public function getById($id);

    /**
     * Save
     *
     * @param \Webkul\RewardSystem\Model\ReferralDetail $subject
     * @return \Webkul\RewardSystem\Model\ReferralDetail
     */
    public function save(\Webkul\RewardSystem\Model\ReferralDetail $subject);

    /**
     * Get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete
     *
     * @param \Webkul\RewardSystem\Model\ReferralDetail $subject
     * @return boolean
     */
    public function delete(\Webkul\RewardSystem\Model\ReferralDetail $subject);

    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
