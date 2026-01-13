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

use \Magento\Framework\App\Config\ScopeConfigInterface;

class Ordermode extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface
    ) {
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
    }

    /**
     * Array of options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $retrunArray = [
            'invoice' => __('Invoice Created'),
            'order_state' => __('Order Complete'),
            'shipment' => __('Shipment Generate')
        ];
        return $retrunArray;
    }
}
