<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @api
 */
interface CompanyRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\CompanyAccount\Api\Data\CompanyInterface $company
     * @throws CouldNotSaveException
     * @throws LocalizedException
     *
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function save(\Amasty\CompanyAccount\Api\Data\CompanyInterface $company);

    /**
     * Get by id
     *
     * @param int $companyId
     * @param bool $withExtensions
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     * @throws NoSuchEntityException
     */
    public function getById($companyId, bool $withExtensions = false);

    /**
     * @param bool $withExtensions
     * @return \Amasty\CompanyAccount\Api\Data\CompanyInterface
     */
    public function getNew(bool $withExtensions = false): \Amasty\CompanyAccount\Api\Data\CompanyInterface;

    /**
     * @param string $fieldName
     * @param string $value
     * @param bool $withExtensions
     * @return \Amasty\CompanyAccount\Model\Company
     */
    public function getByField($fieldName, $value, bool $withExtensions = false);

    /**
     * Delete
     *
     * @param \Amasty\CompanyAccount\Api\Data\CompanyInterface $company
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\CompanyAccount\Api\Data\CompanyInterface $company);

    /**
     * Delete by id
     *
     * @param int $companyId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($companyId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
