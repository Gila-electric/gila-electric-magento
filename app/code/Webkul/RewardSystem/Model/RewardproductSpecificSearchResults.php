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

use Webkul\RewardSystem\Api\Data\RewardproductSpecificSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with RewardproductSpecific search results.
 */
class RewardproductSpecificSearchResults extends SearchResults implements RewardproductSpecificSearchResultsInterface
{
}
