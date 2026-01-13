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

namespace Webkul\RewardSystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Webkul\RewardSystem\Model\ResourceModel\Rewarddetail;
use Webkul\RewardSystem\Model\ResourceModel\Rewardrecord;
use Magento\Sales\Model\OrderFactory;

/**
 * CustomerRewardDetail resolver, used for GraphQL request processing
 */
class CustomerRewardDetail implements ResolverInterface
{
    /**
     * @var Webkul\RewardSystem\Model\ResourceModel\Rewardrecord\CollectionFactory
     */
    protected $rewardRecordCollection;

    /**
     * @var Webkul\RewardSystem\Model\ResourceModel\RewardDetail\CollectionFactory
     */
    protected $rewardDetailCollection;

    /**
     * @var OrderFactory
     */
    protected $order;

    /**
     * @var Webkul\RewardSystem\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param Rewardrecord\CollectionFactory $rewardRecordCollection
     * @param Rewarddetail\CollectionFactory $rewardDetailCollection
     * @param OrderFactory $order
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        Rewardrecord\CollectionFactory $rewardRecordCollection,
        Rewarddetail\CollectionFactory $rewardDetailCollection,
        OrderFactory $order,
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->rewardRecordCollection = $rewardRecordCollection;
        $this->rewardDetailCollection = $rewardDetailCollection;
        $this->order = $order;
        $this->helper = $helper;
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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $customerId = $context->getUserId();
        $returnArray = [];

        $remainingPoints = $this->getRemainingRewardPoints($customerId);
        $rewardDetails = $this->getRewardDetails($customerId, $args);

        $returnArray['remaining_reward_point'] = $remainingPoints;
        $returnArray['reward_details'] = $rewardDetails;
        return $returnArray;
    }

    /**
     * Get Remaining Reward Points
     *
     * @param int $customerId
     */
    public function getRemainingRewardPoints($customerId)
    {
        $remainingPoints = 0;
        $rewardRecordCollection = $this->rewardRecordCollection->create()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if (count($rewardRecordCollection)) {
            foreach ($rewardRecordCollection as $record) {
                $remainingPoints = $record->getRemainingRewardPoint();
            }
        }
        return $remainingPoints;
    }

    /**
     * Get reward details of a customer
     *
     * @param integer $customerId
     * @param array|null $args
     * @return array
     */
    public function getRewardDetails($customerId, $args)
    {
        $rewardDetailCollection = $this->getRewardDetailCollection($customerId, $args);
        $rewardDetails = [];
        foreach ($rewardDetailCollection as $record) {
            if ($record->getOrderId()) {
                $order = $block->getOrder()->load($record->getOrderId());
                $incrementId = $order->getIncrementId();
                $description = __('Order id: ') . '#' . $incrementId;
                if (strpos($record->getTransactionNote(), 'in way') !== false) {
                    $description =  $description . ' ' . __('Credited amount in way');
                }
            } else {
                $description = __($record->getTransactionNote());
            }

            $type = $record->getAction() == 'credit' ? __("Credit") : __("Debit");

            if ($record->getStatus()) {
                if ($record->getAction() == 'debit') {
                    $status = __('Applied');
                } elseif ($record->getAction() == 'expire') {
                    $status = __('Expired');
                } elseif ($record->getStatus() == 2) {
                    $status = __('Cancelled');
                } else {
                    $status = __('Approved');
                }
            } else {
                $status = __('Pending');
            }

            $expiresAt = $record->getAction() == 'debit' ? "-" : $this->helper->formatDate($record->getExpiresAt());

            $rewardDetails[] = [
                'description' => $description,
                'type' => $type,
                'reward_point' => $record->getRewardPoint(),
                'status' => $status,
                'expires_at' => $expiresAt
            ];
        }
        return $rewardDetails;
    }

    /**
     * Get reward detail collection of a customer
     *
     * @param integer $customerId
     * @param array|null $args
     * @return object
     */
    public function getRewardDetailCollection($customerId, $args)
    {
        $param = $args['filter'] ?? [];

        $rewardDetailCollection = $this->rewardDetailCollection->create()
            ->addFieldToFilter('customer_id', $customerId);
        if (!isset($param['srt'])) {
            $rewardDetailCollection->setOrder('transaction_at', 'DESC');
        }

        if (isset($param['status']) && $param['status']) {
            $status;
            if ($param['status'] == 'APPLIED') {
                $param['status'] = 'debit';
                $status = 1;
            } elseif ($param['status'] == 'APPROVED') {
                $param['status'] = 'credit';
                $status = 1;
            } elseif ($param['status'] == 'PENDING') {
                $param['status'] = 'credit';
                $status = 0;
            } elseif ($param['status'] == 'EXPIRED') {
                $param['status'] = 'expire';
                $status = 1;
            } elseif ($param['status'] == 'CANCELLED') {
                $param['status'] = 'credit';
                $status = 2;
            } else {
                $param['status'] = $param['status'] ;
                $status = 1;
            }
            $rewardDetailCollection->addFieldToFilter('status', $status)
                ->addFieldToFilter('action', $param['status']);
        }
        if (isset($param['type']) && $param['type']) {
            $rewardDetailCollection->addFieldToFilter('action', strtolower($param['type']));
        }
        if (isset($param['reward_point']) && $param['reward_point']) {
            $rewardDetailCollection->addFieldToFilter('reward_point', $param['reward_point']);
        }

        if (isset($param['srt']) && $param['srt']) {
            $rewardDetailCollection->setOrder('reward_point', $param['srt']);
        }
        return $rewardDetailCollection;
    }
}
