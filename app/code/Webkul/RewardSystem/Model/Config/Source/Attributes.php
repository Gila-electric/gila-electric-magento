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

namespace Webkul\RewardSystem\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Attributes extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $appConfigScopeConfigInterface;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * Constructor
     *
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface,
        CollectionFactory $collectionFactory
    ) {
        $this->appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Array of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->collectionFactory->create()->setOrder('frontend_label', 'ASC');
        $result = [];
        foreach ($collection as $items) {
            $data['value'] = $items->getAttributeCode();
            $data['label'] = $items->getFrontendLabel();
            array_push($result, $data);
        }
        
        return $result;
    }
}
