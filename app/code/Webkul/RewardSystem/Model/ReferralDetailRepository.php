<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_RewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\RewardSystem\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Webkul\RewardSystem\Api\Data\ReferralDetailSearchResultsInterfaceFactory;

class ReferralDetailRepository implements \Webkul\RewardSystem\Api\ReferralDetailRepositoryInterface
{
    /**
     * @var \Webkul\RewardSystem\Model\ReferralDetailFactory
     */
    protected $modelFactory = null;

    /**
     * @var \Webkul\RewardSystem\Model\ResourceModel\ReferralDetail\CollectionFactory
     */
    protected $collectionFactory = null;

    /**
     * @var ReferralDetailSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * Constructor
     *
     * @param \Webkul\RewardSystem\Model\ReferralDetailFactory $modelFactory
     * @param \Webkul\RewardSystem\Model\ResourceModel\ReferralDetail\CollectionFactory $collectionFactory
     * @param ReferralDetailSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Webkul\RewardSystem\Model\ReferralDetailFactory $modelFactory,
        \Webkul\RewardSystem\Model\ResourceModel\ReferralDetail\CollectionFactory $collectionFactory,
        ReferralDetailSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\RewardSystem\Model\ReferralDetail
     */
    public function getById($id)
    {
        $model = $this->modelFactory->create()->load($id);
        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The model with the "%1" ID doesn\'t exist.', $id)
            );
        }
        return $model;
    }

    /**
     * Save
     *
     * @param \Webkul\RewardSystem\Model\ReferralDetail $subject
     * @return \Webkul\RewardSystem\Model\ReferralDetail
     */
    public function save(\Webkul\RewardSystem\Model\ReferralDetail $subject)
    {
        try {
            $subject->save();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($exception->getMessage()));
        }
        return $subject;
    }

    /**
     * Get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete
     *
     * @param \Webkul\RewardSystem\Model\ReferralDetail $subject
     * @return boolean
     */
    public function delete(\Webkul\RewardSystem\Model\ReferralDetail $subject)
    {
        try {
            $subject->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
