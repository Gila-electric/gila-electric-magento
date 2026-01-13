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
use Webkul\RewardSystem\Api\RewardorderDetailRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\RewardSystem\Model\ResourceModel\RewardorderDetail as RewardorderDetailResource;
use Webkul\RewardSystem\Model\ResourceModel\RewardorderDetail\CollectionFactory as RewardorderDetailCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Webkul\RewardSystem\Api\Data\RewardorderDetailSearchResultsInterface;

/**
 * Class TimeslotConfigRepository
 */
class RewardorderDetailRepository implements RewardorderDetailRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $rewardOrderDetailFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $rewardOrderDetailCollectionFactory;

    /**
     * @var Data\RewardorderDetailSearchResultsInterfaceFactory
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
     * @param RewardorderDetailResource $resource
     * @param RewardorderDetailFactory $rewardOrderDetailFactory
     * @param RewardorderDetailCollection $rewardOrderDetailCollectionFactory
     * @param Data\RewardorderDetailSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        RewardorderDetailResource $resource,
        RewardorderDetailFactory $rewardOrderDetailFactory,
        RewardorderDetailCollection $rewardOrderDetailCollectionFactory,
        Data\RewardorderDetailSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->rewardOrderDetailFactory = $rewardOrderDetailFactory;
        $this->rewardOrderDetailCollectionFactory = $rewardOrderDetailCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Function Save
     *
     * @param Data\RewardorderDetailInterface $rewardOrderDetail
     * @return PreorderComplete
     * @throws CouldNotSaveException
     */
    public function save(Data\RewardorderDetailInterface $rewardOrderDetail)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $rewardOrderDetail->setStoreId($storeId);
        try {
            $this->resource->save($rewardOrderDetail);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $rewardOrderDetail;
    }

    /**
     * Function Get By Id
     *
     * @param string $id
     * @return PreorderComplete
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $rewardOrderDetail = $this->rewardOrderDetailFactory->create();
        $this->resource->load($rewardOrderDetail, $id);
        if (!$rewardOrderDetail->getEntityId()) {
            throw new NoSuchEntityException(__('Time Slot with id "%1" does not exist.', $id));
        }
        return $rewardOrderDetail;
    }

    /**
     * Load RewardorderDetail data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return RewardorderDetailSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->rewardOrderDetailCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Function Delete
     *
     * @param Data\RewardorderDetailInterface $rewardOrderDetail
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\RewardorderDetailInterface $rewardOrderDetail)
    {
        try {
            $this->resource->delete($rewardOrderDetail);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Function Delete by Id
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
