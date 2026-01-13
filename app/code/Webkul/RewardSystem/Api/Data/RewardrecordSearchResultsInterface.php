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
 * Interface for Rewardrecord search results.
 */
interface RewardrecordSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Rewardrecord list.
     *
     * @return \Webkul\RewardSystem\Api\Data\RewardrecordInterface[]
     */
    public function getItems();

    /**
     * Set Rewardrecord list.
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardrecordInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
