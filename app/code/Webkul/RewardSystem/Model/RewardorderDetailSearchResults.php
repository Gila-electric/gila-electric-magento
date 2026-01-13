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

use Webkul\RewardSystem\Api\Data\RewardorderDetailSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with RewardorderDetail search results.
 */
class RewardorderDetailSearchResults extends SearchResults implements RewardorderDetailSearchResultsInterface
{
}
