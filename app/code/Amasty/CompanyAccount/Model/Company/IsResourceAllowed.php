<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package B2B Company Account for Magento 2
 */

namespace Amasty\CompanyAccount\Model\Company;

use Amasty\CompanyAccount\Api\PermissionRepositoryInterface;

class IsResourceAllowed
{
    /**
     * @var PermissionRepositoryInterface
     */
    private $permissionRepository;

    public function __construct(PermissionRepositoryInterface $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function checkResourceForRole(string $resource, int $roleId): bool
    {
        $permissions = $this->permissionRepository->getByRoleId($roleId);
        foreach ($permissions->getItems() as $permission) {
            if ($resource === $permission->getResourceId()) {
                return true;
            }
        }

        return false;
    }
}
