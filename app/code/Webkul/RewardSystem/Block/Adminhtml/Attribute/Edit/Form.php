<?php
/**
 * Block\Adminhtml\Attribute\Edit Form.php
 *
 * @category Webkul
 * @package Webkul_RewardSystem
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\RewardSystem\Block\Adminhtml\Attribute\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param \Webkul\RewardSystem\Helper\Data        $helper
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webkul\RewardSystem\Helper\Data $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('edit_form');
        $this->setTitle(__('Reward system Attribute Rule'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $helper = $this->_helper;
        $optionsList = $helper->getOptionsList();
        $id = $this->getRequest()->getParam('id');
        $model = $this->_coreRegistry->registry('reward_attributerewardData');
        $form = $this->_formFactory->create(
            ['data' =>
                ['id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post']
            ]
        );
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );
        if ($model && $model->getEntityId()) {
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        }
        $fieldset->addField(
            'points',
            'text',
            [
                'name' => 'points',
                'label' => __('Reward Point'),
                'required' => true,
                'class' =>  'required-entry validate-greater-than-zero',
                'placeholder'=>'Points'
            ]
        );
        if ($id) {
            $fieldset->addField(
                'option_id',
                'select',
                [
                 'name' => 'option_id',
                 'label' => __('Attribute Options'),
                 'id' => 'option_id',
                 'title' => __('Attribute Options'),
                 'values' => $optionsList,
                 'disabled' => true
                ]
            );
        } else {
            $fieldset->addField(
                'option_id',
                'select',
                [
                'name' => 'option_id',
                'label' => __('Attribute Options'),
                'id' => 'option_id',
                'title' => __('Attribute Options'),
                'values' => $optionsList,
                'class' => 'required-entry',
                'required' => true
                ]
            );
        }
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Rule Status'),
                'title' => __('Rule Status'),
                'name' => 'status',
                'required' => true,
                'options' => [
                    1 => __('Enabled'),
                    0 => __('Disabled')
                ]
            ]
        );
        if ($model) {
            $form->setValues($model->getData());
        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
