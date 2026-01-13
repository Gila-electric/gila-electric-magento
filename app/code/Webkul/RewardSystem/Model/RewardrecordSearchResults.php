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

use Webkul\RewardSystem\Api\Data\RewardrecordSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Rewardrecord search results.
 */
class RewardrecordSearchResults extends SearchResults implements RewardrecordSearchResultsInterface
{
}
