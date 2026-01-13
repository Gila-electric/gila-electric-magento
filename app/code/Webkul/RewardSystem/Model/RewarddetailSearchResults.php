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
declare(strict_types=1);

namespace Webkul\RewardSystem\Model;

use Webkul\RewardSystem\Api\Data\RewarddetailSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Rewarddetail search results.
 */
class RewarddetailSearchResults extends SearchResults implements RewarddetailSearchResultsInterface
{
}
