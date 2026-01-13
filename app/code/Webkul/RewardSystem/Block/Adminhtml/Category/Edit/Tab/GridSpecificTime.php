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

namespace Webkul\RewardSystem\Block\Adminhtml\Category\Edit\Tab;

use Webkul\RewardSystem\Model\CategoryFactory;
use Webkul\RewardSystem\Model\ResourceModel\RewardcategorySpecific\CollectionFactory as RewardCategoryCollection;
use Magento\Catalog\Helper\Category;
use Webkul\RewardSystem\Helper\Data;

class GridSpecificTime extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Webkul\RewardSystem\Model\CategoryFactory
     */
    protected $_category;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var RewardCategoryCollection
     */
    protected $_rewardCategoryCollection;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

     /**
      * @param \Magento\Backend\Block\Template\Context            $context
      * @param \Magento\Backend\Helper\Data                       $backendHelper
      * @param CategoryFactory                                    $categoryFactory
      * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute  $eavAttribute
      * @param RewardCategoryCollection                           $rewardCategoryCollection
      * @param \Magento\Framework\App\ProductMetadataInterface    $productMetadata
      * @param array                                              $data
      */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CategoryFactory $categoryFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        RewardCategoryCollection $rewardCategoryCollection,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->_category = $categoryFactory;
        $this->_eavAttribute = $eavAttribute;
        $this->_rewardCategoryCollection = $rewardCategoryCollection;
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
        $this->setId('rewardcategorygridspecifictime');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
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
            'rewardsystem/category/gridspecific',
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
        $collection = $this->_category->create()->getCollection();
        $proAttrId = $this->_eavAttribute->getIdByCode("catalog_category", "name");
        if ($this->productMetadata->getEdition()=='Enterprise') {
            $entity_id='row_id';
        } else {
            $entity_id='entity_id';
        }
        $collection->getSelect()->joinLeft(
            ['cpev'=>$collection->getTable('catalog_category_entity_varchar')],
            'main_table.entity_id = cpev.'.$entity_id,
            ['category_name'=>'value']
        )->where("cpev.store_id = 0 AND cpev.attribute_id = ".$proAttrId);

        $collection->getSelect()->joinLeft(
            ['rc'=>$collection->getTable('wk_rs_reward_category_specific')],
            'main_table.entity_id = rc.category_id',
            ['points'=>'points',"status"=>'status', 'start_time'=>'start_time','end_time'=>'end_time']
        );

        $collection->addFilterToMap("category_name", "cpev.value");
        $collection->addFilterToMap("points", "rc.points");
        $collection->addFilterToMap("status", "rc.status");
        $collection->addFilterToMap("start_time", "rc.start_time");
        $collection->addFilterToMap("end_time", "rc.end_time");

        $this->setCollection($collection);
        parent::_prepareCollection();
    }

    /**
     * Set Collection Order
     *
     * @param object $columns
     */
    protected function _setCollectionOrder($columns)
    {
        if ($columns->getOrderCallback()) {
            //phpcs:disable
            call_user_func_array($columns->getOrderCallback(), $this->getCollection(), $columns);
            //phpcs:enable
            return $this;
        }
        return parent::_setCollectionOrder($columns);
    }

    /**
     * Prepare Columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_category',
            [
                'type' => 'checkbox',
                'name' => 'in_category',
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Category ID'),
                'index' =>  'entity_id',
                'filter_index'  => 'main_table.entity_id',
                'class' =>  'productid'
            ]
        );
        $this->addColumn(
            'category_name',
            [
                'header'                    => __('Category Name'),
                'index'                     => 'category_name',
                'filter_index'              => '`cpev`.`value`',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filterCategoryName'],
                'order_callback'            => [$this, 'sortCategoryName']
            ]
        );
        $this->addColumn(
            'points',
            [
                'header' => __('Reward Points'),
                'index' => 'points',
                'filter_index'              => '`rc`.`points`',
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
                'filter_index'              => '`rc`.`start_time`',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filterStartTime'],
                'order_callback'            => [$this, 'sortStartTime'],
                'renderer'  => \Webkul\RewardSystem\Block\Adminhtml\Category\Edit\Tab\Renderer\StartTime::class
            ]
        );
        $this->addColumn(
            'end_time',
            [
                'header' => __('End Time'),
                'index' => 'end_time',
                'filter' => false,
                'filter_index'              => '`rc`.`end_time`',
                'gmtoffset'                 => true,
                'filter_condition_callback' => [$this, 'filterEndTime'],
                'order_callback'            => [$this, 'sortEndTime'],
                'renderer'  => \Webkul\RewardSystem\Block\Adminhtml\Category\Edit\Tab\Renderer\EndTime::class
            ]
        );
        $this->addColumn(
            "status",
            [
                "header"    => __("Status"),
                "index"     => "status",
                'type' => 'options',
                'options' => $this->_getBasedOnOptions(),
                'filter_index'              => '`rc`.`status`',
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
     * Filter Category Name
     *
     * @param object $collection
     * @param object $column
     */
    public function filterCategoryName($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }

        $condition = $collection->getConnection()
            ->prepareSqlCondition('cpev.value', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }

    /**
     * Sort Category Name
     *
     * @param object $collection
     * @param object $column
     */
    public function sortCategoryName($collection, $column)
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
            ->prepareSqlCondition('rc.status', $column->getFilter()->getCondition());
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
            ->prepareSqlCondition('rc.points', $column->getFilter()->getCondition());
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
            ->prepareSqlCondition('rc.start_time', $column->getFilter()->getCondition());
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
            ->prepareSqlCondition('rc.end_time', $column->getFilter()->getCondition());
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
