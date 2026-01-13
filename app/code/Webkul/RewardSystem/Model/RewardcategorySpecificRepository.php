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

namespace Webkul\RewardSystem\Model;

use Webkul\RewardSystem\Api\Data;
use Webkul\RewardSystem\Api\RewardcategorySpecificRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\RewardSystem\Model\ResourceModel\RewardcategorySpecific as RewardCategoryResource;
use Webkul\RewardSystem\Model\ResourceModel\RewardcategorySpecific\CollectionFactory as RewardCategoryCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Webkul\RewardSystem\Api\Data\RewardcategorySpecificSearchResultsInterface;

/**
 * Class RewardcategorySpecificRepository is used for the reward category for specific time
 */
class RewardcategorySpecificRepository implements RewardcategorySpecificRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $rewardcategorySpecificFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $rewardCategoryCollectionFactory;

    /**
     * @var Data\RewardcategorySpecificSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param RewardCategoryResource $resource
     * @param RewardcategorySpecificFactory $rewardcategorySpecificFactory
     * @param RewardCategoryCollection $rewardCategoryCollectionFactory
     * @param Data\RewardcategorySpecificSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        RewardCategoryResource $resource,
        RewardcategorySpecificFactory $rewardcategorySpecificFactory,
        RewardCategoryCollection $rewardCategoryCollectionFactory,
        Data\RewardcategorySpecificSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->rewardcategorySpecificFactory = $rewardcategorySpecificFactory;
        $this->rewardCategoryCollectionFactory = $rewardCategoryCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save Preorder Complete data
     *
     * @param Data\RewardcategorySpecificInterface $rewardCategory
     * @return PreorderComplete
     * @throws CouldNotSaveException
     */
    public function save(Data\RewardcategorySpecificInterface $rewardCategory)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $rewardCategory->setStoreId($storeId);
        try {
            $this->resource->save($rewardCategory);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $rewardCategory;
    }

    /**
     * Load Preorder Complete data by given Block Identity
     *
     * @param string $id
     * @return PreorderComplete
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $rewardCategory = $this->rewardcategorySpecificFactory->create();
        $this->resource->load($rewardCategory, $id);
        if (!$rewardCategory->getEntityId()) {
            throw new NoSuchEntityException(__('Reward Category with id "%1" does not exist.', $id));
        }
        return $rewardCategory;
    }

    /**
     * Load RewardcategorySpecific data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return RewardcategorySpecificSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->rewardCategoryCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete PreorderComplete
     *
     * @param Data\RewardcategorySpecificInterface $rewardCategory
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\RewardcategorySpecificInterface $rewardCategory)
    {
        try {
            $this->resource->delete($rewardCategory);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete PreorderComplete by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
