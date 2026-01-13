<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\RewardSystem\Model\Resolver\Admin;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\Webapi\Authorization;

/**
 * This class is responsible for validating the admin request
 */
class AdminRequestValidator
{
    public const ADMIN_USER_TYPE = 2;

    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * Initialize dependencies
     *
     * @param Authorization $authorization
     */
    public function __construct(
        Authorization $authorization
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Validate request
     *
     * @param \Magento\GraphQl\Model\Query\ContextInterface $context
     * @throws GraphQlAuthorizationException
     * @return void
     */
    public function validate($context)
    {
        if ($context->getUserType() != self::ADMIN_USER_TYPE) {
            throw new GraphQlAuthorizationException(__('Unauthorized access. Only admin can access this information.'));
        }
        /** $this->checkPermissions(); */
    }

    /**
     * Perform authorization.
     *
     * Taken help from Magento/Webapi/Controller/Rest/RequestValidator to implement it.
     *
     * @throws \Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException
     * @return void
     */
    private function checkPermissions()
    {
        $aclResources = ['Webkul_RewardSystem::rewardsystem'];

        if (!$this->authorization->isAllowed($aclResources)) {
            $params = ['resources' => implode(', ', $aclResources)];
            throw new GraphQlAuthorizationException(
                __("The consumer isn't authorized to access %resources.", $params)
            );
        }
    }
}
