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
use Magento\Sales\Api\Data\OrderInterface;

class OrderTotal
{
    /**
     * After resolve
     *
     * @param \Magento\SalesGraphQl\Model\Resolver\OrderTotal $subject
     * @param array $result
     * @return array
     */
    public function afterResolve(
        \Magento\SalesGraphQl\Model\Resolver\OrderTotal $subject,
        $result
    ) {
        if (!(($result['model'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var OrderInterface $order */
        $orderModel = $result['model'];
        $result['order'] = $orderModel;

        return $result;
    }
}
