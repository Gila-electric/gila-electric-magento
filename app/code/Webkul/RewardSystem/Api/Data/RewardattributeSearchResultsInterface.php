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
 * Interface for Rewardattribute search results.
 */
interface RewardattributeSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Rewardattribute list.
     *
     * @return \Webkul\RewardSystem\Api\Data\RewardattributeInterface[]
     */
    public function getItems();

    /**
     * Set Rewardattribute list.
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardattributeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
