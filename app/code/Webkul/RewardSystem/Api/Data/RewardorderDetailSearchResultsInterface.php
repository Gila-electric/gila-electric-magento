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
 * Interface for RewardorderDetail search results.
 */
interface RewardorderDetailSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RewardorderDetail list.
     *
     * @return \Webkul\RewardSystem\Api\Data\RewardorderDetailInterface[]
     */
    public function getItems();

    /**
     * Set RewardorderDetail list.
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardorderDetailInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
