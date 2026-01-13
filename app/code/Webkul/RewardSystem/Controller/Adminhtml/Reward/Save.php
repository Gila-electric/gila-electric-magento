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
namespace Webkul\RewardSystem\Controller\Adminhtml\Reward;

use Magento\Backend\App\Action\Context;
use Webkul\RewardSystem\Helper\Data as HelperData;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Webkul\RewardSystem\Controller\Adminhtml\Reward\Save
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     *  Execute
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $this->dataPersistor->set('transaction', $data);
                $this->processSave($data);
                $this->dataPersistor->clear('transaction');
                $this->messageManager->addSuccessMessage(__('Transaction is saved successfully!!'));
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the transaction.')
                );
            }
            return $resultRedirect->setPath('*/*/new');
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Process Save
     *
     * @param array $data
     * @throws LocalizedException
     * @return void
     */
    private function processSave(array $data)
    {
        $errs = [];
        if (isset($data['customer_selections'])) {
            $rewardData = [
              'points' => $data['reward_point'],
              'type' => $data['action'],
              'review_id' => 0,
              'order_id' => 0,
              'status' => 1,
              'is_revert' => 0,
              'note' => $data['transaction_note']
            ];
            foreach ($data['customer_selections'] as $customerData) {
                $rewardData['customer_id'] = $customerData['customer_id'];
                if ($data['action'] == 'credit') {
                    $msg = __(
                        'You got %1 reward points from admin',
                        $data['reward_point']
                    )->render();
                    $adminMsg = __(
                        '%1 customer has been credited with %2 reward points',
                        $customerData['customer_name'],
                        $data['reward_point']
                    )->render();
                } else {
                    $msg = __(
                        '%1 reward points debited by the admin',
                        $data['reward_point']
                    )->render();
                    $adminMsg = __(
                        '%1 customer has been debited with %2 reward points',
                        $customerData['customer_name'],
                        $data['reward_point']
                    )->render();
                }
                $res = $this->helperData->setDataFromAdmin(
                    $msg,
                    $adminMsg,
                    $rewardData
                );
                if (isset($res[0]) && isset($res[1]) && !$res[0]) {
                    $errs[] = $res[1];
                }
            }
            $errs = array_unique($errs);
            foreach ($errs as $err) {
                $this->messageManager->addErrorMessage(__($err));
            }
        } else {
            throw new LocalizedException(
                __('No customers added, Please select customers')
            );
        }
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_RewardSystem::reward');
    }
}
