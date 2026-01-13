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
 * Interface for RewardcategorySpecific search results.
 */
interface RewardcategorySpecificSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get RewardcategorySpecific list.
     *
     * @return \Webkul\RewardSystem\Api\Data\RewardcategorySpecificInterface[]
     */
    public function getItems();

    /**
     * Set RewardcategorySpecific list.
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardcategorySpecificInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
