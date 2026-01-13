<?php
/**
 * Webkul_DailyDeals Product form.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\DailyDeals\Plugin\Block\Adminhtml\Product\Edit\Action\Attribute\Tab;

class Attributes
{

    /**
     * @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute
     */
    protected $_attributeAction;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    private $productAttribute;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeAction
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $productAttribute
     */
    public function __construct(
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeAction,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $productAttribute
    ) {
        $this->_attributeAction = $attributeAction;
        $this->productAttribute = $productAttribute;
    }
    
    /**
     * Before Get Attribute
     *
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Attributes $subject
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $result
     * @return \Magento\Catalog\Helper\Product\Edit\Action\Attribute
     */
    public function afterGetAttributes(
        \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Attributes $subject,
        $result
    ) {
        $attributeIds = [];
        $attributeIds[] = $this->productAttribute->getIdByCode(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_to_date'
        );
        $attributeIds[] = $this->productAttribute->getIdByCode(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_from_date'
        );
        foreach ($attributeIds as $attributeId) {
            if (isset($result[$attributeId])) {
                $var = $result[$attributeId]->getData();
                $var['frontend_input'] = 'date';
                $var['frontend_input_renderer'] = \Webkul\DailyDeals\Block\Renderer\DateTime::class;
                $result[$attributeId]->setData($var);
            }
        }
        $result = $this->_attributeAction->getAttributes()->getItems();
        return $result;
    }
}
