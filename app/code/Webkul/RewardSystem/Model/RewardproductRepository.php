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
use Webkul\RewardSystem\Api\RewardproductRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\RewardSystem\Model\ResourceModel\Rewardproduct as RewardProductResource;
use Webkul\RewardSystem\Model\ResourceModel\Rewardproduct\CollectionFactory as RewardProductCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Webkul\RewardSystem\Api\Data\RewardproductSearchResultsInterface;

/**
 * Class TimeslotConfigRepository
 */
class RewardproductRepository implements RewardproductRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $rewardProductFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $rewardProductCollectionFactory;

    /**
     * @var Data\RewardproductSearchResultsInterfaceFactory
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
     * @param RewardProductResource $resource
     * @param RewardrecordFactory $rewardProductFactory
     * @param RewardProductCollection $rewardProductCollectionFactory
     * @param Data\RewardproductSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        RewardProductResource $resource,
        RewardrecordFactory $rewardProductFactory,
        RewardProductCollection $rewardProductCollectionFactory,
        Data\RewardproductSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->rewardProductFactory = $rewardProductFactory;
        $this->rewardProductCollectionFactory = $rewardProductCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save Preorder Complete data
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\RewardproductInterface $rewardProduct
     * @return PreorderComplete
     * @throws CouldNotSaveException
     */
    public function save(Data\RewardproductInterface $rewardProduct)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $rewardProduct->setStoreId($storeId);
        try {
            $this->resource->save($rewardProduct);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $rewardProduct;
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
        $rewardProduct = $this->rewardProductFactory->create();
        $this->resource->load($rewardProduct, $id);
        if (!$rewardProduct->getEntityId()) {
            throw new NoSuchEntityException(__('Reward Product with id "%1" does not exist.', $id));
        }
        return $rewardProduct;
    }

    /**
     * Load Rewardproduct data collection by given search criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return RewardproductSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $collection = $this->rewardProductCollectionFactory->create();

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
     * @param \Webkul\MarketplacePreorder\Api\Data\Data\RewardproductInterface $rewardProduct
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\RewardproductInterface $rewardProduct)
    {
        try {
            $this->resource->delete($rewardProduct);
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
