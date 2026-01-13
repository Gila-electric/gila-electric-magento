<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Source\Company;

use Magento\User\Model\ResourceModel\User\CollectionFactory as AdminUserCollectionFactory;

class AdminUser implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var AdminUserCollectionFactory
     */
    private $adminUserCollectionFactory;

    public function __construct(AdminUserCollectionFactory $adminUserCollectionFactory)
    {
        $this->adminUserCollectionFactory = $adminUserCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray() : array
    {
        $options = [];
        $adminUserCollection = $this->adminUserCollectionFactory->create();
        foreach ($adminUserCollection as $user) {
            $options[] = [
                'label' => $user->getFirstName() . ' ' . $user->getLastName(),
                'value' => $user->getId()
            ];
        }

        return $options;
    }
}
