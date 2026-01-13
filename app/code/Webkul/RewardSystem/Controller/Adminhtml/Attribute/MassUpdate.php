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

namespace Webkul\RewardSystem\Controller\Adminhtml\Attribute;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\RewardSystem\Model\ResourceModel\Rewardattribute\CollectionFactory;
use Webkul\RewardSystem\Controller\Adminhtml\Attribute as AttributeController;

class MassUpdate extends AttributeController
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $status = $this->getRequest()->getParam('attributeruleupdate');
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $countRecord = $collection->getSize();
        foreach ($collection as $item) {
            $item->setStatus($status);
            $item->save();
        }
        $this->messageManager->addSuccess(
            __(
                'A total of %1 record(s) have been updated.',
                $countRecord
            )
        );
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
}
