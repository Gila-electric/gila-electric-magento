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

namespace Webkul\RewardSystem\Controller\Adminhtml\Attribute;

use Webkul\RewardSystem\Controller\Adminhtml\Attribute as AttributeController;
use Magento\Backend\App\Action;
use Webkul\RewardSystem\Model\RewardattributeFactory;

class Save extends AttributeController
{
    /**
     * @var Webkul\RewardSystem\Model\RewardattributeFactory
     */
    protected $_rewardattributeFactory;

    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param Action\Context                              $context
     * @param RewardattributeFactory                      $rewardattributeFactory
     * @param \Webkul\RewardSystem\Helper\Data            $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        Action\Context $context,
        RewardattributeFactory $rewardattributeFactory,
        \Webkul\RewardSystem\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_rewardattributeFactory = $rewardattributeFactory;
        $this->helper = $helper;
        $this->_date = $date;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $attributeCode = $this->helper->getAttributeCode();
        $optionsList = $this->helper->getOptionsList();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_rewardattributeFactory->create();
            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                $model->load($id);
                $data['attribute_code'] = $model->getAttributeCode();
                $data['option_label'] = $model->getOptionLabel();
                $data['option_id'] = $model->getOptionId();
            } else {
                $duplicate = $this->checkForAlreadyExists($data);
                if ($duplicate) {
                    $this->messageManager->addError(__("Rule already exists for this option."));
                    return $resultRedirect->setPath('*/*/index');
                }
                $data['created_at'] = $this->_date->gmtDate();
                $data['attribute_code'] = $attributeCode;
                $data['option_label'] = isset($optionsList[$data['option_id']]) ? $optionsList[$data['option_id']] : '';
            }
            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccess(
                    __('Attribute Rule successfully saved.')
                );
                $this->_session
                    ->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'id' => $model->getEntityId(),
                            '_current' => true
                        ]
                    );
                }
                return $resultRedirect->setPath('*/*/index');
            } catch (\Magento\Framework\Exception\LocalizedException $error) {
                $this->messageManager->addError($error->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving the data.')
                );
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath(
                '*/*/edit',
                ['id' => $this->getRequest()->getParam('entity_id')]
            );
        }
        return $resultRedirect->setPath('*/*/index');
    }

    /**
     * Check For Already Exists
     *
     * @param array $data
     */
    public function checkForAlreadyExists($data)
    {
        $attributeCode = $this->helper->getAttributeCode();
        $rewardattributeSet = $this->_rewardattributeFactory->create()->getCollection()
                                  ->addFieldToFilter('option_id', ['eq'=>$data['option_id']])
                                  ->addFieldToFilter('attribute_code', ['eq'=>$attributeCode]);
        if (isset($data['entity_id'])) {
            $rewardattributeSet->addFieldToFilter("entity_id", ["neq" =>$data['entity_id']]);
        }

        if (count($rewardattributeSet)) {
            return true;
        }
        return false;
    }
}
