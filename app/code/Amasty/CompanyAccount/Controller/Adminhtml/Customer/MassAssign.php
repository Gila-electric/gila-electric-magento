<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Controller\Adminhtml\Customer;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;

class MassAssign extends Action
{
    public const ADMIN_RESOURCE = 'Amasty_CompanyAccount::company_management';
    public const CUSTOMER_IDS_PARAM_NAME = 'customerIds';
    public const COMPANY_ID_PARAM_NAME = 'companyId';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CompanyRepositoryInterface
     */
    protected $companyRepository;

    public function __construct(
        Context $context,
        ?Filter $filter, // @deprecated
        CompanyRepositoryInterface $companyRepository,
        ?CollectionFactory $collectionFactory // @deprecated
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->companyRepository = $companyRepository;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Execute action
     *
     * @return Json
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $customerIds = (array)$this->getRequest()->getParam(self::CUSTOMER_IDS_PARAM_NAME);
        $customerIds = array_map('intval', $customerIds);
        $companyId = (int)$this->getRequest()->getParam(self::COMPANY_ID_PARAM_NAME);
        if (!$companyId || empty($customerIds)) {
            return $resultJson->setData([
                'error' => [__('We can\'t assign customers to the company.')]
            ]);
        }
        try {
            $company = $this->companyRepository->getById($companyId);
            $company->addCustomerIds($customerIds);
            $this->companyRepository->save($company);
        } catch (NoSuchEntityException $e) {
            return $resultJson->setData(['error' => [__('This Company no longer exists.')]]);
        } catch (CouldNotSaveException $e) {
            return $resultJson->setData(['error' => [__('Something went wrong.')]]);
        } catch (LocalizedException $e) {
            return $resultJson->setData(['error' => [__($e->getMessage())]]);
        }

        return $resultJson->setData(['success' => [__('A total of %1 record(s) have been saved.', count($customerIds))]]);
    }
}
