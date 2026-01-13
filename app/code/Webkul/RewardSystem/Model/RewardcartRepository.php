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
use Webkul\RewardSystem\Api\RewardcartRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\RewardSystem\Model\ResourceModel\Rewardcart as RewardCartResource;
use Webkul\RewardSystem\Model\ResourceModel\Rewardcart\CollectionFactory as RewardCartCollection;
use Webkul\RewardSystem\Model\RewardcartFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Webkul\RewardSystem\Api\Data\RewardcartSearchResultsInterface;

/**
 * Class RewardcartRepository is used for the reward cart updation
 */
class RewardcartRepository implements RewardcartRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var \Webkul\RewardSystem\Model\RewardcartFactory
     */
    protected $rewardCartFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $rewardCartCollectionFactory;

    /**
     * @var Data\RewardcartSearchResultsInterfaceFactory
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
     * @param RewardCartResource $resource
     * @param RewardCartCollection $rewardCartCollectionFactory
     * @param RewardcartFactory $rewardCartFactory
     * @param Data\RewardcartSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        RewardCartResource $resource,
        RewardCartCollection $rewardCartCollectionFactory,
        RewardcartFactory $rewardCartFactory,
        Data\RewardcartSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->rewardCartFactory = $rewardCartFactory;
        $this->rewardCartCollectionFactory = $rewardCartCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save Reward Cart data
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardcartInterface $rewardCart
     * @return Rewardcart
     * @throws CouldNotSaveException
     */
    public function save(Data\RewardcartInterface $rewardCart)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $rewardCart->setStoreId($storeId);
        try {
            $this->resource->save($rewardCart);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $rewardCart;
    }

    /**
     * Load Reward Cart data by given Block Identity
     *
     * @param string $id
     * @return RewaredCart
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $rewardCart = $this->rewardCartFactory->create();
        $this->resource->load($rewardCart, $id);
        if (!$rewardCart->getEntityId()) {
            throw new NoSuchEntityException(__('Reward Cart with id "%1" does not exist.', $id));
        }
        return $rewardCart;
    }

    /**
     * Load Rewardcart data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return RewardcartSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->rewardCartCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Reward Cart
     *
     * @param \Webkul\RewardSystem\Api\Data\RewardcartInterface $rewardCart
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\RewardcartInterface $rewardCart)
    {
        try {
            $this->resource->delete($rewardCart);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Reward Cart by given Block Identity
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
