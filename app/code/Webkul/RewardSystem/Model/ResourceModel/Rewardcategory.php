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

namespace Webkul\RewardSystem\Model\ResourceModel;

use Webkul\RewardSystem\Api\Data\RewardcategoryInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DB\Select;

class Rewardcategory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var EntityManager
     */
    protected $_entityManager;

    /**
     * @var MetadataPool
     */
    protected $_metadataPool;
    
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @param Context $context
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        $this->_entityManager = $entityManager;
        $this->_metadataPool = $metadataPool;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('wk_rs_reward_category', 'entity_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->_metadataPool->getMetadata(RewardcategoryInterface::class)->getEntityConnection();
    }

    /**
     * Get RewardCategory By Id
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param bool $field
     * @return bool|int|string
     * @throws LocalizedException
     * @throws \Exception
     */
    private function getRewardCategoryById(AbstractModel $object, $value, $field = null)
    {
        $categoryMetadata = $this->_metadataPool->getMetadata(RewardcategoryInterface::class);
        if (!is_numeric($value) && $field === null) {
            $field = 'identifier';
        } elseif (!$field) {
            $field = $categoryMetadata->getIdentifierField();
        }
        $entityId = $value;

        if ($field != $categoryMetadata->getIdentifierField()) {
            $select = $this->_getLoadSelect($field, $value, $object);

            $select->reset(Select::COLUMNS)
                ->columns($this->getMainTable() . '.' . $categoryMetadata->getIdentifierField())
                ->limit(1);
            $result = $this->getConnection()->fetchCol($select);

            $entityId = count($result) ? $result[0] : false;
        }
        return $entityId;
    }

    /**
     * Load an object
     *
     * @param \Magento\Cms\Model\Block|AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $blockId = $this->getRewardCategoryById($object, $value, $field);
        if ($blockId) {
            $this->_entityManager->load($object, $blockId);
        }
        return $this;
    }
     /**
      * Retrieve select object for load object data
      *
      * @param string $field
      * @param mixed $value
      * @param \Magento\Cms\Model\Block|AbstractModel $object
      * @return Select
      */
    protected function _getLoadSelect($field, $value, $object)
    {
        $entityMetadata = $this->_metadataPool->getMetadata(RewardcategoryInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = parent::_getLoadSelect($field, $value, $object);

        return $select;
    }

    /**
     * Set store model
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }

    /**
     * Save
     *
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function save(AbstractModel $object)
    {
        $this->_entityManager->save($object);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function delete(AbstractModel $object)
    {
        $this->_entityManager->delete($object);
        return $this;
    }
}
