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

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for Rewarddetail search results.
 */
interface RewarddetailSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Rewarddetail list.
     *
     * @return \Webkul\RewardSystem\Api\Data\RewarddetailInterface[]
     */
    public function getItems();

    /**
     * Set Rewarddetail list.
     *
     * @param \Webkul\RewardSystem\Api\Data\RewarddetailInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
