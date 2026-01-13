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

namespace Webkul\RewardSystem\Model\Resolver\Admin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * AddRewardTransaction resolver, used for GraphQL request processing
 */
class AddRewardTransaction implements ResolverInterface
{
    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AdminRequestValidator
     */
    protected $adminRequestValidator;

    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $helper;

    /**
     * Array to return response
     *
     * @var array
     */
    protected $returnArray = [];

    /**
     * Constructor
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param AdminRequestValidator $adminRequestValidator
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        AdminRequestValidator $adminRequestValidator,
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->customerRepository = $customerRepository;
        $this->adminRequestValidator = $adminRequestValidator;
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
        $this->adminRequestValidator->validate($context);

        if (!isset($args['input'])) {
            throw new GraphQlInputException(
                __("'input' input argument is required.")
            );
        }
        try {
            $data = $args['input'];
            if (empty($data['customerIds']) || !is_array($data['customerIds'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Customer id(s) cannot be empty.')
                );
            }
            // As per coding standard, we have used enum values in SCREAMING_SNAKE_CASE format in graphql schema.
            $data['action'] = strtolower($data['action']);
            $this->processSave($data);
            $this->returnArray['message'][] = __('Transaction is saved successfully!!');
            $this->returnArray['status'] = self::SUCCESS;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray['message'][] = $e->getMessage();
            $this->returnArray['status'] = self::LOCAL_ERROR;
        } catch (\Exception $e) {
            $this->returnArray['message'][] = __('Something went wrong while saving the transaction.');
            $this->returnArray['status'] = self::SEVERE_ERROR;
        }
        return $this->returnArray;
    }

    /**
     * Process Save
     *
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function processSave(array $data)
    {
        $errs = [];
        $rewardData = [
            'points' => $data['reward_point'],
            'type' => $data['action'],
            'review_id' => 0,
            'order_id' => 0,
            'status' => 1,
            'is_revert' => 0,
            'note' => $data['transaction_note']
        ];
        foreach ($data['customerIds'] as $customerId) {
            $customerData = $this->getCustomerData($customerId);
            if (empty($customerData)) {
                continue;
            }
            $rewardData['customer_id'] = $customerData['customer_id'];
            if ($data['action'] == 'credit') {
                $msg = __(
                    'You got %1 reward points from admin',
                    $data['reward_point']
                )->render();
                $adminMsg = __(
                    '%1 customer has been credited with %2 reward points',
                    $customerData['customer_name'],
                    $data['reward_point']
                )->render();
            } else {
                $msg = __(
                    '%1 reward points debited by the admin',
                    $data['reward_point']
                )->render();
                $adminMsg = __(
                    '%1 customer has been debited with %2 reward points',
                    $customerData['customer_name'],
                    $data['reward_point']
                )->render();
            }
            $res = $this->helper->setDataFromAdmin(
                $msg,
                $adminMsg,
                $rewardData
            );
            if (isset($res[0]) && isset($res[1]) && !$res[0]) {
                $errs[] = $res[1];
            }
        }
        $errs = array_unique($errs);
        foreach ($errs as $err) {
            $this->returnArray['message'][] = __($err);
        }
    }

    /**
     * Function get customer data
     *
     * @param int $customerId
     * @return array
     */
    private function getCustomerData($customerId)
    {
        $customerData = [];
        $customer = $this->helper->loadCustomer($customerId);
        if ($customer->getId()) {
            $customerData = [
                'customer_id' => $customerId,
                'customer_name' => $customer->getName()
            ];
        }
        return $customerData;
    }
}
