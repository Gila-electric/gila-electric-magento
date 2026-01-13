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
use Webkul\RewardSystem\Api\RewardrecordRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\RewardSystem\Model\ResourceModel\Rewardrecord as RewardRecordResource;
use Webkul\RewardSystem\Model\ResourceModel\Rewardrecord\CollectionFactory as RewardRecordCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Webkul\RewardSystem\Api\Data\RewardrecordSearchResultsInterface;

/**
 * Class TimeslotConfigRepository
 */
class RewardrecordRepository implements RewardrecordRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $rewardRecordFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $rewardRecordCollectionFactory;

    /**
     * @var Data\RewardrecordSearchResultsInterfaceFactory
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
     * @param RewardRecordResource $resource
     * @param RewardrecordFactory $rewardRecordFactory
     * @param RewardRecordCollection $rewardRecordCollectionFactory
     * @param Data\RewardrecordSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        RewardRecordResource $resource,
        RewardrecordFactory $rewardRecordFactory,
        RewardRecordCollection $rewardRecordCollectionFactory,
        Data\RewardrecordSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->rewardRecordFactory = $rewardRecordFactory;
        $this->rewardRecordCollectionFactory = $rewardRecordCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save Preorder Complete data
     *
     * @param Webkul\RewardSystem\Api\Data\RewardrecordInterface $rewardRecord
     * @return PreorderComplete
     * @throws CouldNotSaveException
     */
    public function save(Data\RewardrecordInterface $rewardRecord)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $rewardRecord->setStoreId($storeId);
        try {
            $this->resource->save($rewardRecord);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $rewardRecord;
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
        $rewardRecord = $this->rewardRecordFactory->create();
        $this->resource->load($rewardRecord, $id);
        if (!$rewardRecord->getEntityId()) {
            throw new NoSuchEntityException(__('Reward record with id "%1" does not exist.', $id));
        }
        return $rewardRecord;
    }

    /**
     * Load Rewardrecord data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return RewardrecordSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->rewardRecordCollectionFactory->create();

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
     * @param Webkul\RewardSystem\Api\Data\RewardrecordInterface $rewardRecord
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\RewardrecordInterface $rewardRecord)
    {
        try {
            $this->resource->delete($rewardRecord);
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

    /**
     * Load Rewardrecord data joined collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return RewardrecordSearchResultsInterface
     */
    public function getJoinedList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->rewardRecordCollectionFactory->create();

        $customerGridFlat = $collection->getTable('customer_grid_flat');
        $collection->getSelect()->join(
            $customerGridFlat.' as cgf',
            'main_table.customer_id = cgf.entity_id',
            [
                'name' => 'name',
                'email' => 'email'
            ]
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
