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

namespace Webkul\RewardSystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\RewardSystem\Helper\Data as RewardSystemHelper;
use Webkul\RewardSystem\Api\Data\RewardrecordInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Webkul\RewardSystem\Api\RewardrecordRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class EditCustomer implements ObserverInterface
{
    /**
     * @var RewardSystemHelper
     */
    protected $_rewardSystemHelper;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
     /**
      * @var DataObjectHelper
      */
    protected $_dataObjectHelper;

    /**
     * @var RewardrecordInterfaceFactory
     */
    protected $_rewardRecordInterface;

    /**
     * @var DateTime
     */
    protected $_date;

    /**
     * @var RewardrecordRepositoryInterface
     */
    protected $_rewardRecordRepository;

    /**
     * @var _CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $formdata;

    /**
     * @param RewardSystemHelper                      $rewardSystemHelper
     * @param DataObjectHelper                        $dataObjectHelper
     * @param RewardrecordInterfaceFactory            $rewardRecordInterface
     * @param ManagerInterface                        $messageManager
     * @param RewardrecordRepositoryInterface         $rewardRecordRepository
     * @param DateTime                                $datetime
     * @param CustomerRepositoryInterface             $customerRepository
     * @param \Magento\Framework\App\Request\Http     $formdata
     */
    public function __construct(
        RewardSystemHelper $rewardSystemHelper,
        DataObjectHelper $dataObjectHelper,
        RewardrecordInterfaceFactory $rewardRecordInterface,
        ManagerInterface $messageManager,
        RewardrecordRepositoryInterface $rewardRecordRepository,
        DateTime $datetime,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\Request\Http $formdata
    ) {
        $this->_rewardSystemHelper = $rewardSystemHelper;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_rewardRecordInterface = $rewardRecordInterface;
        $this->_messageManager = $messageManager;
        $this->_rewardRecordRepository = $rewardRecordRepository;
        $this->_date = $datetime;
        $this->_customerRepository = $customerRepository;
        $this->formdata = $formdata;
    }
    /**
     * Cart save after observer.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
         $customer = $observer->getEvent()->getData();
         $customerEmail = $customer['email'];
         $customerData = $this->_customerRepository->get($customerEmail);
        $dob = $this->formdata->getParam('dob');
        if ($dob != null) {
            $customerData->setDob($dob);
            $this->_customerRepository->save($customerData);
        }
    }
}
