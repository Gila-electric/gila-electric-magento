<?php
/**
 * Webkul Software
 *
 * @category Magento
 * @package  Webkul_DailyDeals
 * @author   Webkul Software Private Limited
 * @license  https://store.webkul.com/license.html
 */
namespace Webkul\DailyDeals\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableDailyDeals extends Command
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var \Magento\Framework\Module\Status
     */
    protected $_modStatus;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Eav\Model\Entity\Attribute $entityAttribute
     * @param \Magento\Framework\Module\Status $modStatus
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Framework\Module\Status $modStatus,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Framework\App\State $state
    ) {
        $this->_resource = $resource;
        $this->_moduleManager = $moduleManager;
        $this->_eavAttribute = $entityAttribute;
        $this->_modStatus = $modStatus;
        $this->productCollection = $productCollection;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('dailydeal:disable')
            ->setDescription('Daily Deal Disable Command');
        parent::configure();
    }
    
    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        if ($this->_moduleManager->isEnabled('Webkul_DailyDeals')) {
            $collection = $this->productCollection->create()->addAttributeToFilter('deal_status', 1);
            foreach ($collection as $product) {
                $product->setSpecialToDate('');
                $product->setSpecialFromDate('');
                $product->setSpecialPrice(null);
                $this->saveObject($product);
            }
            $connection = $this->_resource
                ->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            
            // delete deal product attribute
            $this->_eavAttribute->loadByCode('catalog_product', 'deal_status')->delete();
            $this->_eavAttribute->loadByCode('catalog_product', 'deal_discount_type')->delete();
            $this->_eavAttribute->loadByCode('catalog_product', 'deal_discount_percentage')->delete();
            $this->_eavAttribute->loadByCode('catalog_product', 'deal_value')->delete();
            $this->_eavAttribute->loadByCode('catalog_product', 'deal_from_date')->delete();
            $this->_eavAttribute->loadByCode('catalog_product', 'deal_to_date')->delete();

            // disable daily deals
            $this->_modStatus->setIsEnabled(false, ['Webkul_DailyDeals']);

            // delete entry from setup_module table
            $tableName = $this->_resource->getTableName('setup_module');
            $whereConditions = [
                $connection->quoteInto('module = ?', 'Webkul_DailyDeals'),
            ];
            $connection->delete($tableName, $whereConditions);

            // delete entry from patch_list table
            $tableName = $this->_resource->getTableName('patch_list');
            $patchName = \Webkul\DailyDeals\Setup\Patch\Data\CreateAttributes::class;
            $whereConditions = [
                $connection->quoteInto('patch_name = ?', $patchName),
            ];
            $connection->delete($tableName, $whereConditions);

            $output->writeln('<info>Webkul Daily Deals has been disabled successfully.</info>');
        }
        return 1;
    }

    /**
     * Save object
     *
     * @param object $object
     * @return void
     */
    private function saveObject($object)
    {
        $object->save();
    }
}
