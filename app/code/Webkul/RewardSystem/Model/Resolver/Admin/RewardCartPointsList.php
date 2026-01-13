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
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Webkul\RewardSystem\Api\RewardcartRepositoryInterface;
use Webkul\RewardSystem\Model\Resolver\Admin\AdminRequestValidator;

/**
 * RewardCartPointsList resolver, used for GraphQL request processing
 */
class RewardCartPointsList implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

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
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RewardcartRepositoryInterface $rewardCartRepository
     * @param AdminRequestValidator $adminRequestValidator
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RewardcartRepositoryInterface $rewardCartRepository,
        AdminRequestValidator $adminRequestValidator,
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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

        if (!isset($args['filter'])) {
            throw new GraphQlInputException(
                __("'filter' input argument is required.")
            );
        }
        $fieldName = key($args['filter']);
        $filterType = key($args['filter'][$fieldName]);
        $fieldValue = $args['filter'][$fieldName][$filterType];
        $searchCriteria = $this->searchCriteriaBuilder->addFilter($fieldName, $fieldValue, $filterType)->create();
        return $this->rewardCartRepository->getList($searchCriteria)->__toArray();
    }
}
