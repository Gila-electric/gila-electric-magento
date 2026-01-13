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
use Webkul\RewardSystem\Model\RewardattributeFactory;
use Webkul\RewardSystem\Model\RewardattributeRepository;

/**
 * SaveRewardAttributePoints resolver, used for GraphQL request processing
 */
class SaveRewardAttributePoints implements ResolverInterface
{
    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var \Webkul\RewardSystem\Controller\Adminhtml\Attribute\Save
     */
    protected $attributePointsSaveController;

    /**
     * @var Webkul\RewardSystem\Model\RewardattributeFactory
     */
    protected $rewardAttributeFactory;

    /**
     * @var Webkul\RewardSystem\Model\RewardattributeRepository
     */
    protected $rewardAttributeRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

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
     * @param \Webkul\RewardSystem\Controller\Adminhtml\Attribute\Save $attributePointsSaveController
     * @param RewardattributeFactory $rewardAttributeFactory
     * @param RewardattributeRepository $rewardAttributeRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param AdminRequestValidator $adminRequestValidator
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        \Webkul\RewardSystem\Controller\Adminhtml\Attribute\Save $attributePointsSaveController,
        RewardattributeFactory $rewardAttributeFactory,
        RewardattributeRepository $rewardAttributeRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        AdminRequestValidator $adminRequestValidator,
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->attributePointsSaveController = $attributePointsSaveController;
        $this->rewardAttributeFactory = $rewardAttributeFactory;
        $this->rewardAttributeRepository = $rewardAttributeRepository;
        $this->date = $date;
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
            $data = $args['input'];
            $attributeCode = $this->helper->getAttributeCode();
            $optionsList = $this->helper->getOptionsList();
            $model = $this->rewardAttributeFactory->create();
            $id = $data['entity_id'] ?? 0;
            if ($id) {
                // to check reward attribute id is valid or not
                $model = $this->rewardAttributeRepository->getById($id);
                $data['attribute_code'] = $model->getAttributeCode();
                $data['option_label'] = $model->getOptionLabel();
                $data['option_id'] = $model->getOptionId();
            } else {
                if (empty($data['option_id'])) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Option id is required.")
                    );
                }
                $duplicate = $this->attributePointsSaveController->checkForAlreadyExists($data);
                if ($duplicate) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Rule already exists for this option.")
                    );
                }
                $data['created_at'] = $this->date->gmtDate();
                $data['attribute_code'] = $attributeCode;
                $data['option_label'] = isset($optionsList[$data['option_id']]) ? $optionsList[$data['option_id']] : '';
                unset($data['entity_id']);
            }
            $model->setData($data);
            $this->rewardAttributeRepository->save($model);

            $returnArray['message'] = __('Attribute Rule successfully saved.');
            $returnArray['status'] = self::SUCCESS;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
        } catch (\RuntimeException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::SEVERE_ERROR;
        } catch (\Exception $e) {
            $returnArray['message'] = __('Something went wrong while saving the data.');
            $returnArray['status'] = self::SEVERE_ERROR;
        }
        return $returnArray;
    }
}
