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
use Webkul\RewardSystem\Api\RewardattributeRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\RewardSystem\Model\ResourceModel\Rewardattribute as RewardattributeResource;
use Webkul\RewardSystem\Model\ResourceModel\Rewardattribute\CollectionFactory as RewardattributeCollection;
use Webkul\RewardSystem\Model\RewardattributeFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Webkul\RewardSystem\Api\Data\RewardattributeSearchResultsInterface;

/**
 * Class RewardattributeRepository is used for reward attribute updation
 */
class RewardattributeRepository implements RewardattributeRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var \Webkul\RewardSystem\Model\RewardattributeFactory
     */
    protected $rewardattributeFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $rewardattributeCollectionFactory;

    /**
     * @var Data\RewardattributeSearchResultsInterfaceFactory
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
     * @param RewardattributeResource $resource
     * @param RewardattributeCollection $rewardattributeCollectionFactory
     * @param RewardattributeFactory $rewardattributeFactory
     * @param Data\RewardattributeSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        RewardattributeResource $resource,
        RewardattributeCollection $rewardattributeCollectionFactory,
        RewardattributeFactory $rewardattributeFactory,
        Data\RewardattributeSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->rewardattributeFactory = $rewardattributeFactory;
        $this->rewardattributeCollectionFactory = $rewardattributeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save Reward Attribute data
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardattributeInterface $rewardAttribute
     * @return RewardAttribute
     * @throws CouldNotSaveException
     */
    public function save(Data\RewardattributeInterface $rewardAttribute)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $rewardAttribute->setStoreId($storeId);
        try {
            $this->resource->save($rewardAttribute);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $rewardAttribute;
    }

    /**
     * Load Reward Attribute data by given Block Identity
     *
     * @param string $id
     * @return RewardAttribute
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $rewardAttribute = $this->rewardattributeFactory->create();
        $this->resource->load($rewardAttribute, $id);
        if (!$rewardAttribute->getEntityId()) {
            throw new NoSuchEntityException(__('Reward Attribute with id "%1" does not exist.', $id));
        }
        return $rewardAttribute;
    }

    /**
     * Load Rewardattribute data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return RewardattributeSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->rewardattributeCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Reward Attribute
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardattributeInterface $rewardAttribute
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\RewardattributeInterface $rewardAttribute)
    {
        try {
            $this->resource->delete($rewardAttribute);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Reward Attribute by given Block Identity
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
