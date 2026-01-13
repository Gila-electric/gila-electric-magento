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

namespace Webkul\RewardSystem\Plugin\GraphQl\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\OrderInterface;

class CreditMemoTotal
{
    /**
     * Around Resolve
     *
     * @param \Magento\SalesGraphQl\Model\Resolver\CreditMemo\CreditMemoTotal $subject
     * @param callable $proceed
     * @param Field $field
     * @param object $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function aroundResolve(
        \Magento\SalesGraphQl\Model\Resolver\CreditMemo\CreditMemoTotal $subject,
        callable $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!(($value['order'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"order" value should be specified'));
        }

        /** @var OrderInterface $orderModel */
        $orderModel = $value['order'];

        $result = $proceed(
            $field,
            $context,
            $info,
            $value,
            $args
        );

        $result['order'] = $orderModel;
        return $result;
    }
}
