<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_RewardSystem
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\RewardSystem\Block\Adminhtml\Product\Edit\Tab;

use Magento\Catalog\Model\ProductFactory;
use Webkul\RewardSystem\Model\ResourceModel\RewardproductSpecific\CollectionFactory as RewardProductCollection;
use Webkul\RewardSystem\Helper\Data;

class GridSpecificTime extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var RewardProductCollection
     */
    protected $_rewardProductCollection;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

     /**
      * @param \Magento\Backend\Block\Template\Context           $context
      * @param \Magento\Backend\Helper\Data                      $backendHelper
      * @param ProductFactory                                    $productFactory
      * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
      * @param RewardProductCollection                           $rewardProductCollection
      * @param \Magento\Framework\App\ProductMetadataInterface   $productMetadata
      * @param array                                             $data
      */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        ProductFactory $productFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        RewardProductCollection $rewardProductCollection,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_eavAttribute = $eavAttribute;
        $this->_rewardProductCollection = $rewardProductCollection;
        $this->productMetadata = $productMetadata;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rewardproductgridspecifictime');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    /**
     * Get Grid Url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'rewardsystem/product/gridspecific',
            ['_current' => true]
        );
    }
    /**
     * Prepare Collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productFactory->create()->getCollection()
        ->addFieldToFilter('type_id', ['nin' => ['grouped']]);

        $proAttrId = $this->_eavAttribute->getIdByCode("catalog_product", "name");
        if ($this->productMetadata->getEdition()=='Enterprise') {
            $entity_id='row_id';
        } else {
            $entity_id='entity_id';
        }
        $collection->getSelect()->joinLeft(
            ['cpev'=>$collection->getTable('catalog_product_entity_varchar')],
            'e.entity_id = cpev.'.$entity_id,
            ['product_name'=>'value']
        )->where("cpev.store_id = 0 AND cpev.attribute_id = ".$proAttrId);

        $collection->getSelect()->joinLeft(
            ['rp'=>$collection->getTable('wk_rs_reward_products_specific')],
            'e.entity_id = rp.product_id',
            ['points'=>'points',"status"=>'status','start_time'=>'start_time','end_time'=>'end_time']
        );
        $collection->addFilterToMap("product_name", "cpev.value");
        $collection->addFilterToMap("points", "rp.points");
        $collection->addFilterToMap("status", "rp.status");
        $collection->addFilterToMap("start_time", "rp.start_time");
        $collection->addFilterToMap("end_time", "rp.end_time");

         $this->setCollection($collection);
         parent::_prepareCollection();
    }

    /**
     * Set Collection Order
     *
     * @param object $column
     */
    protected function _setCollectionOrder($column)
    {
        if ($column->getOrderCallback()) {
            //phpcs:disable
            call_user_func_array($column->getOrderCallback(), $this->getCollection(), $column);
            //phpcs:enable
            return $this;
        }

        return parent::_setCollectionOrder($column);
    }

    /**
     * Prepare Columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_product',
            [
                'type' => 'checkbox',
                'name' => 'in_product',
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Product ID'),
                'index' =>  'entity_id',
                'class' =>  'productid'
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Product SKU'),
                'index' =>  'sku',
                'class' =>  'productsku'
            ]
        );
        $this->addColumn(
            'product_name',
            [
                'header'                    => __('Product Name'),
                'index'                     => 'product_name',
                'filter_index'              => '`cpev`.`value`',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filterProductName'],
                'order_callback'            => [$this, 'sortProductName']
            ]
        );
        $this->addColumn(
            'points',
            [
                'header' => __('Reward Points'),
                'index' => 'points',
                'filter_index'              => '`rp`.`points`',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filterPoints'],
                'order_callback'            => [$this, 'sortPoints']
            ]
        );
        $this->addColumn(
            'start_time',
            [
                'header' => __('Start Time'),
                'index' => 'start_time',
                'filter' => false,
                'filter_index'              => '`rp`.`start_time`',
                'gmtoffset'                 => true,
                // 'filter_condition_callback' => [$this, 'filterStartTime'],
                'order_callback'            => [$this, 'sortStartTime'],
                'renderer'  => \Webkul\RewardSystem\Block\Adminhtml\Product\Edit\Tab\Renderer\StartTime::class
            ]
        );
        $this->addColumn(
            'end_time',
            [
                'header' => __('End Time'),
                'index' => 'end_time',
                'filter' => false,
                'filter_index'              => '`rp`.`end_time`',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filterEndTime'],
                'order_callback'            => [$this, 'sortEndTime'],
                'renderer'  => \Webkul\RewardSystem\Block\Adminhtml\Product\Edit\Tab\Renderer\EndTime::class
            ]
        );
        $this->addColumn(
            "status",
            [
                "header"    => __("Status"),
                "index"     => "status",
                'type' => 'options',
                'options' => $this->_getBasedOnOptions(),
                'filter_index'              => '`rp`.`status`',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filterStatus'],
                'order_callback'            => [$this, 'sortStatus']
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Get Based On Options
     */
    protected function _getBasedOnOptions()
    {
        return [
            0=>__('Disabled'),
            1=>__('Enabled')
        ];
    }

    /**
     * Filter Product Name
     *
     * @param object $collection
     * @param object $column
     */
    public function filterProductName($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }

        $condition = $collection->getConnection()
            ->prepareSqlCondition('cpev.value', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    /**
     * Sort Product Name
     *
     * @param object $collection
     * @param object $column
     */
    public function sortProductName($collection, $column)
    {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }

    /**
     * Filter Status
     *
     * @param object $collection
     * @param object $column
     */
    public function filterStatus($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }
        $condition = $collection->getConnection()
            ->prepareSqlCondition('rp.status', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    /**
     * Sort Status
     *
     * @param object $collection
     * @param object $column
     */
    public function sortStatus($collection, $column)
    {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }

    /**
     * Filter Points
     *
     * @param object $collection
     * @param object $column
     */
    public function filterPoints($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }
        $condition = $collection->getConnection()
            ->prepareSqlCondition('rp.points', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    /**
     * Sort Points
     *
     * @param object $collection
     * @param object $column
     */
    public function sortPoints($collection, $column)
    {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }

    /**
     * Filter Start Time
     *
     * @param object $collection
     * @param object $column
     */
    public function filterStartTime($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }
        $condition = $collection->getConnection()
            ->prepareSqlCondition('rp.start_time', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    /**
     * Sort Start Time
     *
     * @param object $collection
     * @param object $column
     */
    public function sortStartTime($collection, $column)
    {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }

     /**
      * Filter End Time
      *
      * @param object $collection
      * @param object $column
      */
    public function filterEndTime($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }
        $condition = $collection->getConnection()
            ->prepareSqlCondition('rp.end_time', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    /**
     * Sort End Time
     *
     * @param object $collection
     * @param object $column
     */
    public function sortEndTime($collection, $column)
    {
        $collection->getSelect()->order($column->getIndex() . ' ' . strtoupper($column->getDir()));
    }
}
