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
 * Interface for RewardproductSpecific search results.
 */
interface RewardproductSpecificSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RewardproductSpecific list.
     *
     * @return \Webkul\RewardSystem\Api\Data\RewardproductSpecificInterface[]
     */
    public function getItems();

    /**
     * Set RewardproductSpecific list.
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardproductSpecificInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
