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
 * Interface for Rewardcart search results.
 */
interface RewardcartSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Rewardcart list.
     *
     * @return \Webkul\RewardSystem\Api\Data\RewardcartInterface[]
     */
    public function getItems();

    /**
     * Set Rewardcart list.
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardcartInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
