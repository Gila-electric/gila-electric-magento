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
namespace Webkul\RewardSystem\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface RewardcategoryRepositoryInterface
{
    /**
     * Save
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardcategoryInterface $items
     */
    public function save(\Webkul\RewardSystem\Api\Data\RewardcategoryInterface $items);

    /**
     * Get By Id
     *
     * @param int $id
     */
    public function getById($id);

    /**
     * Get List
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardcategoryInterface $item
     */
    public function delete(\Webkul\RewardSystem\Api\Data\RewardcategoryInterface $item);

    /**
     * Delete by id
     *
     * @param int $id
     */
    public function deleteById($id);
}
