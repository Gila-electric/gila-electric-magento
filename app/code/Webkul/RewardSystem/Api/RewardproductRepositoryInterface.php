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

interface RewardproductRepositoryInterface
{
    /**
     * Save
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardproductInterface $items
     */
    public function save(\Webkul\RewardSystem\Api\Data\RewardproductInterface $items);

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
     * @param \Webkul\RewardSystem\Api\Data\RewardproductInterface $item
     */
    public function delete(\Webkul\RewardSystem\Api\Data\RewardproductInterface $item);

    /**
     * Delete By Id
     *
     * @param int $id
     */
    public function deleteById($id);
}
