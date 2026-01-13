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
use Webkul\RewardSystem\Model\RewardcartFactory;
use Webkul\RewardSystem\Model\RewardcartRepository;

/**
 * SaveRewardCartPoints resolver, used for GraphQL request processing
 */
class SaveRewardCartPoints implements ResolverInterface
{
    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var \Webkul\RewardSystem\Controller\Adminhtml\Cart\Save
     */
    protected $cartPointsSaveController;

    /**
     * @var Webkul\RewardSystem\Model\RewardcartFactory
     */
    protected $rewardCartFactory;

    /**
     * @var Webkul\RewardSystem\Model\RewardcartRepository
     */
    protected $rewardCartRepository;

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
     * @param \Webkul\RewardSystem\Controller\Adminhtml\Cart\Save $cartPointsSaveController
     * @param RewardcartFactory $rewardCartFactory
     * @param RewardcartRepository $rewardCartRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param AdminRequestValidator $adminRequestValidator
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        \Webkul\RewardSystem\Controller\Adminhtml\Cart\Save $cartPointsSaveController,
        RewardcartFactory $rewardCartFactory,
        RewardcartRepository $rewardCartRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        AdminRequestValidator $adminRequestValidator,
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->cartPointsSaveController = $cartPointsSaveController;
        $this->rewardCartFactory = $rewardCartFactory;
        $this->rewardCartRepository = $rewardCartRepository;
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
            $error = $this->cartPointsSaveController->validateData($data);
            if (count($error) > 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($error[0])
                );
            }
            $duplicate = $this->cartPointsSaveController->checkForAlreadyExists($data);
            if ($duplicate) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Amount range already exists.")
                );
            }
            $model = $this->rewardCartFactory->create();
            $id = $data['entity_id'] ?? 0;
            if ($id) {
                // to check reward cart id is valid or not
                $model = $this->rewardCartRepository->getById($id);
            } else {
                $data['created_at'] = $this->date->gmtDate();
                unset($data['entity_id']);
            }
            $model->setData($data);
            $this->rewardCartRepository->save($model);

            $returnArray['message'] = __('Cart Rule successfully saved.');
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
