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
use Webkul\RewardSystem\Api\RewarddetailRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\RewardSystem\Model\ResourceModel\Rewarddetail as RewardDetailResource;
use Webkul\RewardSystem\Model\ResourceModel\Rewarddetail\CollectionFactory as RewardDetailCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Webkul\RewardSystem\Api\Data\RewarddetailSearchResultsInterface;

/**
 * Class TimeslotConfigRepository
 */
class RewarddetailRepository implements RewarddetailRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $rewardDetailFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $rewardDetailCollectionFactory;

    /**
     * @var Data\RewarddetailSearchResultsInterfaceFactory
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
     * @param RewardDetailResource $resource
     * @param RewarddetailFactory $rewardDetailFactory
     * @param RewardDetailCollection $rewardDetailCollectionFactory
     * @param Data\RewarddetailSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        RewardDetailResource $resource,
        RewarddetailFactory $rewardDetailFactory,
        RewardDetailCollection $rewardDetailCollectionFactory,
        Data\RewarddetailSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->rewardDetailFactory = $rewardDetailFactory;
        $this->rewardDetailCollectionFactory = $rewardDetailCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save Preorder Complete data
     *
     * @param Data\RewarddetailInterface $rewardDetail
     * @return PreorderComplete
     * @throws CouldNotSaveException
     */
    public function save(Data\RewarddetailInterface $rewardDetail)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $rewardDetail->setStoreId($storeId);
        try {
            $this->resource->save($rewardDetail);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $rewardDetail;
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
        $rewardDetail = $this->rewardDetailFactory->create();
        $this->resource->load($rewardDetail, $id);
        if (!$rewardDetail->getEntityId()) {
            throw new NoSuchEntityException(__('The model with id "%1" does not exist.', $id));
        }
        return $rewardDetail;
    }

    /**
     * Load Rewarddetail data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return RewarddetailSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->rewardDetailCollectionFactory->create();

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
     * @param Data\RewarddetailInterface $rewardDetail
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\RewarddetailInterface $rewardDetail)
    {
        try {
            $this->resource->delete($rewardDetail);
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
