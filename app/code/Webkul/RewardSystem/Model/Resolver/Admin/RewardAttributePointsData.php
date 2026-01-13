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
use Webkul\RewardSystem\Api\RewardattributeRepositoryInterface;
use Webkul\RewardSystem\Model\Resolver\Admin\AdminRequestValidator;

/**
 * RewardAttributePointsData resolver, used for GraphQL request processing
 */
class RewardAttributePointsData implements ResolverInterface
{
    /**
     * @var RewardattributeRepositoryInterface
     */
    protected $rewardAttributeRepository;

    /**
     * @var AdminRequestValidator
     */
    protected $adminRequestValidator;

    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param RewardattributeRepositoryInterface $rewardAttributeRepository
     * @param AdminRequestValidator $adminRequestValidator
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        RewardattributeRepositoryInterface $rewardAttributeRepository,
        AdminRequestValidator $adminRequestValidator,
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->rewardAttributeRepository = $rewardAttributeRepository;
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

        $returnArray = [];

        $options = [
            'option_id' => $this->helper->getOptionsValues(),
            'status' => $this->helper->getStatusValues()
        ];
        $returnArray['options'] = $options;

        $id = $args['id'] ?? 0;
        $rewardAttribute = [];
        if ($id) {
            $rewardAttribute = $this->rewardAttributeRepository->getById($id);
        }
        $returnArray['reward_attribute'] = $rewardAttribute;

        return $returnArray;
    }
}
