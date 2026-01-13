<?php

namespace Webkul\DailyDeals\Plugin\Controller\Adminhtml\Product\Action\Attribute;

class Save
{
    /**
     * @var const fixed
     */
    public const FIXED = "fixed";
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute
     */
    protected $attributeHelper;

    /**
     * @var  \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->attributeHelper = $attributeHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->messageManager = $messageManager;
    }
    /**
     * Before Execute
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save  $subject
     * @return void
     */
    public function beforeExecute(
        \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save $subject
    ) {
        $attributesData = $this->request->getParam('attributes', []);

        if (isset($attributesData['deal_value'])
            && isset($attributesData['deal_status'])
            && isset($attributesData['deal_discount_type'])
            && $attributesData['deal_discount_type'] == self::FIXED
        ) {
            $dealValue = $attributesData['deal_value'];
            $productIds = $this->attributeHelper->getProductIds();
            $collection = $this->_productCollectionFactory->create()->addAttributeToSelect('entity_id')
                ->addFieldToFilter('entity_id', ['in' => $productIds])
                ->addAttributeToFilter('price', ['lt' => $dealValue]);
            if ($collection->getSize()) {
                $removeIds = array_column($collection->getData(), 'entity_id');
                $uniqueIds = array_diff($productIds, $removeIds);
                sort($uniqueIds);
                $this->attributeHelper->setProductIds($uniqueIds);
                $implodeIds = implode(',', $removeIds);
                $msg = __(
                    'Product id(s) %1 can\'t be updated because deal value must be less than the product price',
                    $implodeIds
                );
                $this->messageManager->addErrorMessage($msg);
            }
        }
    }
}
