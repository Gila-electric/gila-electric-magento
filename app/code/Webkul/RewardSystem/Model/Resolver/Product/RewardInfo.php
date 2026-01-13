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

namespace Webkul\RewardSystem\Model\Resolver\Product;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
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

        /* @var $product Product */
        $product = $value['model'];

        $helper = $this->rewardHelper;
        $rewardPriorityStatus = $helper->getrewardPriority();
        $enableRewardSystem = $helper->enableRewardSystem();

        $productId = $product->getId();
        $product = $helper->loadProduct($productId);
        list(
            $productRewardPoints,
            $minPrice,
            $maxPrice,
            $pointsRequired,
            $sumOfRewardPoints,
            $minRewardPoint,
            $maxRewardPoint,
            $status,
            $message
        ) = $helper->getProductRewardInfo($product);

        $rewardUseMsg = '';
        $productRewardMsg = '';
        if ($enableRewardSystem) {
            if ($product->getTypeId() == 'grouped'
            || ($product->getTypeId() == 'configurable' && $maxPrice > $minPrice)
            || $product->getTypeId() == 'bundle'
            ) {
                $rewardUseMsg = __(
                    'Between %1 - %2 Reward Points will be used to purchase this product',
                    $minPrice,
                    $maxPrice
                );
            } else {
                $rewardUseMsg = __(
                    '%1 Reward Points will be used to purchase this product',
                    $pointsRequired
                );
            }
        }
        if ($rewardPriorityStatus == 0 && $enableRewardSystem && $productRewardPoints) {
            $productRewardMsg = __('Buy this product and ');
            if ($product->getTypeId() == 'grouped'
            || ($product->getTypeId() == 'configurable' && $sumOfRewardPoints > 0 && $pointsRequired > $minRewardPoint)
            || ($product->getTypeId() == 'bundle' && $pointsRequired > $minRewardPoint)
            ) {
                $productRewardMsg .= __('Earn %1 to %2 Reward Points', $minRewardPoint, $productRewardPoints);
            } else {
                $productRewardMsg .= __('Earn %1 Reward Points', $productRewardPoints);
            }
            if ($status) {
                $productRewardMsg .= __(' between');
            }
            $productRewardMsg .= $message;
        }
        return [
            'reward_use_msg' => $rewardUseMsg,
            'product_reward_msg' => $productRewardMsg
        ];
    }
}
