<?php
/**
 * Block\Adminhtml\Product\Edit\Tab From.php
 *
 * @category Webkul
 * @package Webkul_RewardSystem
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\RewardSystem\Block\Adminhtml\Product\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Customer account form block.
 */
class FormSpecificTime extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @var string
     */
    protected $_template = 'tab/productRewardSpecific.phtml';

    /**
     * @var \Webkul\RewardSystem\Block\Adminhtml\Product\Edit\Tab\GridSpecificTime
     */
    protected $blockGrid;

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Reward points');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Reward points');
    }
    /**
     * Tab class getter.
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }
    /**
     * Return URL link to Tab content.
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }
    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return true;
    }
    /**
     * Can show tab in tabs.
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }
    /**
     * Tab is hidden.
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Init Form
     */
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('_reward');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Set point on product')]
        );
        $fieldset->addField(
            'rewardpoint',
            'text',
            [
                'label' => __('Enter points'),
                'name' => 'rewardpoint',
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Reward point status'),
                'title' => __('Reward point status'),
                'name' => 'status',
                'options' => [1 => __('Enable'), 0 => __('Disable')]
            ]
        );
        $this->setForm($form);
        return $this;
    }

    /**
     * Prepare the layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }

    /**
     * To Html
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    /**
     * Get Block Grid
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \Webkul\RewardSystem\Block\Adminhtml\Product\Edit\Tab\GridSpecificTime::class,
                'rewardproductgridspecifictime'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Get Grid Html
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }
}
