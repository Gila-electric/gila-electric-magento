<?php


namespace Ashmawy\OrderGridFilter\Plugin;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;

/**
 * Class AddDataToOrdersGrid
 */
class AddDataToOrdersGrid
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * AddDataToOrdersGrid constructor.
     *
     * @param \Psr\Log\LoggerInterface $customLogger
     * @param array $data
     */
    public function __construct(
        \Psr\Log\LoggerInterface $customLogger,
        array $data = []
    ) {
        $this->logger = $customLogger;
    }

    /**
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject
     * @param OrderGridCollection $collection
     * @param $requestName
     * @return mixed
     */
    public function afterGetReport($subject, $collection, $requestName)
    {
        if ($requestName !== 'sales_order_grid_data_source') {
            return $collection;
        }

        if ($collection->getMainTable() === $collection->getResource()->getTable('sales_order_grid')) {
            try {
                $orderAddressTableName           = $collection->getResource()->getTable('sales_order_address');
                $directoryCountryRegionTableName = $collection->getResource()->getTable('directory_country_region');
                $salesorderTableName = $collection->getResource()->getTable('sales_order');

                $collection->getSelect()->joinLeft(
                    ['soat' => $orderAddressTableName],
                    'soat.parent_id = main_table.entity_id AND soat.address_type = \'shipping\'',
                    ['']
                );
                $collection->getSelect()->joinLeft(
                    ['dcrt' => $directoryCountryRegionTableName],
                    'soat.region_id = dcrt.region_id',
                    ['code']
                );
                $collection->getSelect()->joinLeft(
                    ['soip' => $salesorderTableName],
                    'main_table.entity_id = soip.entity_id',
                    array('remote_ip','coupon_code')
                );
                // $collection->getSelect()->joinLeft(
                //     ['sosh' => $salesorderStatusHistoryTableName],
                //     'soat.entity_id = sosh.parent_id',
                //     ['comment' => 'GROUP_CONCAT(DISTINCT sosh.comment)']
                // );

                // Add product's Sku column
                $this->addProductsSkuColumn($collection);
                // Add product's Comment column
//                $this->addProductsCommentColumn($collection);
            } catch (\Zend_Db_Select_Exception $selectException) {
                // Do nothing in that case
                $this->logger->log(100, $selectException);
            }
        }

        return $collection;
    }

    /**
     * Adds products Sku column to the orders grid collection
     *
     * @param OrderGridCollection $collection
     * @return OrderGridCollection
     */
    private function addProductsSkuColumn(OrderGridCollection $collection): OrderGridCollection
    {
        // Get original table name
        $orderItemsTableName = $collection->getResource()->getTable('sales_order_item');
        // Create new select instance
        $itemsTableSelectGrouped = $collection->getConnection()->select();
        // Add table with columns which must be selected (skip useless columns)
        $itemsTableSelectGrouped->from(
            $orderItemsTableName,
            [
                'sku'     => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT sku SEPARATOR \',\')'),
                'order_id' => 'order_id'
            ]
        );
        // Group our select to make one column for one order
        $itemsTableSelectGrouped->group('order_id');
        // Add our sub-select to main collection with only one column: sku
        $collection->getSelect()
            ->joinLeft(
                ['soi' => $itemsTableSelectGrouped],
                'soi.order_id = main_table.entity_id',
                ['sku']
            );

        return $collection;
    }
    /**
     * Adds products Comment column to the orders grid collection
     *
     * @param OrderGridCollection $collection
     * @return OrderGridCollection
     */
//    private function addProductsCommentColumn(OrderGridCollection $collection): OrderGridCollection
//    {
//        // Get original table name
//        $salesorderStatusHistoryTableName = $collection->getResource()->getTable('sales_order_status_history');
//        // Create new select instance
//        $ordergriditemsTableSelectGrouped = $collection->getConnection()->select();
//        // Add table with columns which must be selected (skip useless columns)
//        $ordergriditemsTableSelectGrouped->from(
//            $salesorderStatusHistoryTableName,
//            [
//                'comment'     => new \Zend_Db_Expr('GROUP_CONCAT(DISTINCT comment SEPARATOR \',\')'),
//                'parent_id' => 'parent_id'
//            ]
//        );
//        // Group our select to make one column for one order
//        $ordergriditemsTableSelectGrouped->group('parent_id');
//        // Add our sub-select to main collection with only one column: sku
//        $collection->getSelect()
//            ->joinLeft(
//                ['sosh' => $ordergriditemsTableSelectGrouped],
//                'sosh.parent_id = main_table.entity_id',
//                ['comment']
//            );
//        return $collection;
//    }
}
