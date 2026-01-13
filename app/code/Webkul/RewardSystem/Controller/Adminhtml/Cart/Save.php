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

namespace Webkul\RewardSystem\Controller\Adminhtml\Cart;

use Webkul\RewardSystem\Controller\Adminhtml\Cart as CartController;
use Magento\Backend\App\Action;
use Webkul\RewardSystem\Model\RewardcartFactory;

class Save extends CartController
{
    /**
     * @var Webkul\RewardSystem\Model\RewardcartFactory
     */
    protected $_rewardcartFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param Action\Context                              $context
     * @param RewardcartFactory                           $rewardcartFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        Action\Context $context,
        RewardcartFactory $rewardcartFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_rewardcartFactory = $rewardcartFactory;
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
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $error = $this->validateData($data);
            if (count($error)>0) {
                $this->messageManager->addError(__($error[0]));
                return $resultRedirect->setPath('*/*/index');
            }
            $duplicate = $this->checkForAlreadyExists($data);
            if ($duplicate) {
                $this->messageManager->addError(__("Amount range already exists."));
                return $resultRedirect->setPath('*/*/index');
            }
            $model = $this->_rewardcartFactory->create();
            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                $model->load($id);
            } else {
                $data['created_at'] = $this->_date->gmtDate();
            }
            $model->setData($data);
            try {
                $model->save();
                $this->messageManager->addSuccess(
                    __('Cart Rule successfully saved.')
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
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
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
        $rewardCartSet1 = $this->_rewardcartFactory->create()->getCollection()
                                  ->addFieldToFilter('amount_from', ['lteq'=>$data['amount_from']])
                                  ->addFieldToFilter('amount_to', ['gteq'=>$data['amount_to']])
                                  ->addFieldToFilter('start_date', ['lteq'=>$data['start_date']])
                                  ->addFieldToFilter('end_date', ['gteq'=>$data['end_date']]);
        $rewardCartSet2 = $this->_rewardcartFactory->create()->getCollection()
                                  ->addFieldToFilter('amount_from', ['lteq'=>$data['amount_to']])
                                  ->addFieldToFilter('amount_to', ['gteq'=>$data['amount_from']])
                                  ->addFieldToFilter('start_date', ['lteq'=>$data['start_date']])
                                  ->addFieldToFilter('end_date', ['gteq'=>$data['end_date']]);
        if (isset($data['entity_id'])) {
            $rewardCartSet1->addFieldToFilter("entity_id", ["neq" =>$data['entity_id']]);
            $rewardCartSet2->addFieldToFilter("entity_id", ["neq" =>$data['entity_id']]);
        }

        if (count($rewardCartSet1) || count($rewardCartSet2)) {
            return true;
        }
        return false;
    }

    /**
     * Validate Data
     *
     * @param array $data
     */
    public function validateData($data)
    {
        $error = [];
        if ($data['start_date']>$data['end_date']) {
            $error[] = __("End date can not be lesser than start From date.");
        } elseif ($data['amount_from']>=$data['amount_to']) {
            $error[] = __("Amount To can not be less than Amount From");
        } elseif ($data['amount_from']<0 || $data['amount_to']<0) {
            $error[] = __("Amount From or Amount To can not be less than 0");
        }
        return $error;
    }
}
