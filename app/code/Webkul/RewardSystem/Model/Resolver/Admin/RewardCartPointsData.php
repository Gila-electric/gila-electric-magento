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
use Webkul\RewardSystem\Api\RewardcartRepositoryInterface;
use Webkul\RewardSystem\Model\Resolver\Admin\AdminRequestValidator;

/**
 * RewardCartPointsData resolver, used for GraphQL request processing
 */
class RewardCartPointsData implements ResolverInterface
{
    /**
     * @var RewardcartRepositoryInterface
     */
    protected $rewardCartRepository;

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
     * @param RewardcartRepositoryInterface $rewardCartRepository
     * @param AdminRequestValidator $adminRequestValidator
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        RewardcartRepositoryInterface $rewardCartRepository,
        AdminRequestValidator $adminRequestValidator,
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->rewardCartRepository = $rewardCartRepository;
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
            'status' => $this->helper->getStatusValues()
        ];
        $returnArray['options'] = $options;

        $id = $args['id'] ?? 0;
        $rewardAttribute = [];
        if ($id) {
            $rewardAttribute = $this->rewardCartRepository->getById($id);
        }
        $returnArray['reward_cart'] = $rewardAttribute;

        return $returnArray;
    }
}
