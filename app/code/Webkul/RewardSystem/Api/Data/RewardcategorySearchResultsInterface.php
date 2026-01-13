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
 * Interface for Rewardcategory search results.
 */
interface RewardcategorySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Rewardcategory list.
     *
     * @return \Webkul\RewardSystem\Api\Data\RewardcategoryInterface[]
     */
    public function getItems();

    /**
     * Set Rewardcategory list.
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardcategoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
