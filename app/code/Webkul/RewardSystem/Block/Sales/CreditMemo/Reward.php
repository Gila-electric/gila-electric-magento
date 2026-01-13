<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RewardSystem\Block\Sales\CreditMemo;

use Magento\Sales\Model\Order;

class Reward extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * Get Source
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Display Full Summary
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Init Totals
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $title = 'Rewarded Amount';
        $store = $this->getStore();
        if ($this->_order->getRewardAmount()!=0 && $this->_order->getTotalRefunded() == 0) {
            $rewardamount = new \Magento\Framework\DataObject(
                [
                    'code' => 'rewardamount',
                    'strong' => false,
                    'value' => $this->_order->getRewardAmount(),
                    'base_value' => $this->_order->getBaseRewardAmount(),
                    'label' => __($title),
                ]
            );
            $parent->addTotal($rewardamount, 'rewardamount');
        }
        return $this;
    }

    /**
     * Get order store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }
    /**
     * Get Order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Get Label Properties
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get Value Properties
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
