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
 * Interface for ReferralDetail search results.
 */
interface ReferralDetailSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get ReferralDetail list.
     *
     * @return \Webkul\RewardSystem\Api\Data\ReferralDetailInterface[]
     */
    public function getItems();

    /**
     * Set ReferralDetail list.
     *
     * @param \Webkul\RewardSystem\Api\Data\ReferralDetailInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
