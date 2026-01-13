<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
declare(strict_types=1);

namespace Webkul\RewardSystem\Model\Resolver\Category;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Category;
use Webkul\RewardSystem\Helper\Data as RewardHelper;

class RewardInfo implements ResolverInterface
{
    /**
     * @var RewardHelper
     */
    protected $rewardHelper;

    /**
     * @param RewardHelper $rewardHelper
     */
    public function __construct(
        RewardHelper $rewardHelper
    ) {
        $this->rewardHelper = $rewardHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /* @var $category Category */
        $category = $value['model'];

        $helper = $this->rewardHelper;
        $rewardPriorityStatus = $helper->getrewardPriority();
        $enableRewardSystem = $helper->enableRewardSystem();

        $categoryId = $category->getId();
        list(
            $categoryRewardPoints,
            $status,
            $message
        ) = $helper->getCategoryRewardToShow($categoryId);

        $categoryRewardMsg = '';
        if ($rewardPriorityStatus == 2 && $enableRewardSystem == 1 && $categoryRewardPoints) {
            $categoryRewardMsg = __('Buy any product and ');
            $categoryRewardMsg .= __('Earn %1 Reward Points', $categoryRewardPoints);
            if ($status) {
                $categoryRewardMsg .= __(' between');
            }
            $categoryRewardMsg .= $message;
        }
        return [
            'category_reward_msg' => $categoryRewardMsg
        ];
    }
}
