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
declare(strict_types=1);

namespace Webkul\RewardSystem\Model\Resolver\Admin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Webkul\RewardSystem\Model\ResourceModel\Rewardattribute\CollectionFactory;

/**
 * ManageRewardAttributePoints resolver, used for GraphQL request processing
 */
class ManageRewardAttributePoints implements ResolverInterface
{
    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var \Webkul\RewardSystem\Model\ResourceModel\Rewardattribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var AdminRequestValidator
     */
    protected $adminRequestValidator;

    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param AdminRequestValidator $adminRequestValidator
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AdminRequestValidator $adminRequestValidator,
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->adminRequestValidator = $adminRequestValidator;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        $this->adminRequestValidator->validate($context);

        try {
            $returnArray = [];
            if (empty($args['ids']) || !is_array($args['ids'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Reward attribute point id(s) cannot be empty.')
                );
            }
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('entity_id', ['in' => $args['ids']]);
            if ($collection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Reward attribute point id(s) are not valid.')
                );
            }
            $countRecord = $collection->getSize();
            switch ($args['action']) {
                case "DELETE":
                    foreach ($collection as $item) {
                        $this->deleteObject($item);
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) have been deleted.',
                        $countRecord
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                case "ENABLE":
                    foreach ($collection as $item) {
                        $item->setStatus(1);
                        $this->saveObject($item);
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) have been updated.',
                        $countRecord
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                case "DISABLE":
                    foreach ($collection as $item) {
                        $item->setStatus(0);
                        $this->saveObject($item);
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) have been updated.',
                        $countRecord
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                default:
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("'action' input argument is not valid.")
                    );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
        } catch (\Exception $e) {
            $returnArray['message'] = __('Invalid Request');
            $returnArray['status'] = self::SEVERE_ERROR;
        }
        return $returnArray;
    }

    /**
     * Save Object
     *
     * @param Object $object
     */
    protected function saveObject($object)
    {
        $object->save();
    }

    /**
     * Delete Object
     *
     * @param Object $object
     */
    protected function deleteObject($object)
    {
        $object->delete();
    }
}
