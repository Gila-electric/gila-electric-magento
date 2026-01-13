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

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DB\Select;

class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
        $this->_init('catalog_category_entity', 'entity_id');
    }
}
