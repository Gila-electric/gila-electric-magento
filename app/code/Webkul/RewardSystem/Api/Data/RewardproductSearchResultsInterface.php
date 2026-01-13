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
 * Interface for Rewardproduct search results.
 */
interface RewardproductSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Rewardproduct list.
     *
     * @return \Webkul\RewardSystem\Api\Data\RewardproductInterface[]
     */
    public function getItems();

    /**
     * Set Rewardproduct list.
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardproductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
