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
use Webkul\RewardSystem\Api\RewardcategoryRepositoryInterface;
use Webkul\RewardSystem\Model\Resolver\Admin\AdminRequestValidator;
use Webkul\RewardSystem\Model\CategoryFactory;

/**
 * RewardCategoryList resolver, used for GraphQL request processing
 */
class RewardCategoryList implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var RewardcategoryRepositoryInterface
     */
    protected $rewardCategoryRepository;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

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
     * @param RewardcategoryRepositoryInterface $rewardCategoryRepository
     * @param CategoryFactory $categoryFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param AdminRequestValidator $adminRequestValidator
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RewardcategoryRepositoryInterface $rewardCategoryRepository,
        CategoryFactory $categoryFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        AdminRequestValidator $adminRequestValidator,
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->rewardCategoryRepository = $rewardCategoryRepository;
        $this->categoryFactory = $categoryFactory;
        $this->eavAttribute = $eavAttribute;
        $this->productMetadata = $productMetadata;
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

        $collection = $this->getFilteredCollection($args);
        $items = $this->getItems($collection);
        $returnArray['total_count'] = $collection->getSize();
        $returnArray['items'] = $items;

        $options = [
            'status' => $this->helper->getRewardPointStatusValues()
        ];
        $returnArray['options'] = $options;

        return $returnArray;
    }

    /**
     * Function getFilteredCollection
     *
     * @param array $args
     * @return object
     */
    private function getFilteredCollection($args)
    {
        $param = $args['filter'] ?? [];

        $collection = $this->getJoinedCollection();

        if (isset($param['entity_id']) && $param['entity_id']) {
            $collection->addFieldToFilter('entity_id', $param['entity_id']);
        }
        if (isset($param['category_name']) && $param['category_name']) {
            $collection->addFieldToFilter('category_name', ['like' => '%'.$param['category_name'].'%']);
        }
        if (isset($param['points']) && $param['points']) {
            $collection->addFieldToFilter('points', $param['points']);
        }
        if (isset($param['status']) && $param['status']) {
            $collection->addFieldToFilter('status', $param['status']);
        }

        return $collection;
    }

    /**
     * Function getJoinedCollection
     *
     * @return object
     */
    private function getJoinedCollection()
    {
        $collection = $this->categoryFactory->create()->getCollection();
        $proAttrId = $this->eavAttribute->getIdByCode("catalog_category", "name");
        if ($this->productMetadata->getEdition()=='Enterprise') {
            $entity_id='row_id';
        } else {
            $entity_id='entity_id';
        }
        $collection->getSelect()->joinLeft(
            ['cpev'=>$collection->getTable('catalog_category_entity_varchar')],
            'main_table.entity_id = cpev.'.$entity_id,
            ['category_name'=>'value']
        )->where("cpev.store_id = 0 AND cpev.attribute_id = ".$proAttrId);

        $collection->getSelect()->joinLeft(
            ['rc'=>$collection->getTable('wk_rs_reward_category')],
            'main_table.entity_id = rc.category_id',
            ['points'=>'points',"status"=>'status']
        );

        $collection->addFilterToMap("category_name", "cpev.value");
        $collection->addFilterToMap("points", "rc.points");
        $collection->addFilterToMap("status", "rc.status");

        return $collection;
    }

    /**
     * Get Items
     *
     * @param object $collection
     * @return array
     */
    private function getItems($collection)
    {
        $data = $collection->getData();
        return $data;
    }
}
