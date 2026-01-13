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
use Webkul\RewardSystem\Api\Data\RewardcategoryInterfaceFactory;

/**
 * UpdateCategoryRewardPoint resolver, used for GraphQL request processing
 */
class UpdateCategoryRewardPoint implements ResolverInterface
{
    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var RewardcategoryInterfaceFactory
     */
    protected $rewardCategory;

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
     * @param RewardcategoryInterfaceFactory $rewardCategory
     * @param AdminRequestValidator $adminRequestValidator
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        RewardcategoryInterfaceFactory $rewardCategory,
        AdminRequestValidator $adminRequestValidator,
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->rewardCategory = $rewardCategory;
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
            $params = $args['input'];

            $params['rewardpoint'] = $params['rewardpoint'] ?? '';
            $params['status'] = ($params['status'] == 'ENABLE') ? 1 : 0;

            if (empty($params['wk_categoryids']) || !is_array($params['wk_categoryids'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please select categories to set points.')
                );
            }
            if (array_key_exists('rewardpoint', $params) &&
                array_key_exists('status', $params)) {
                $categoryIds = $params['wk_categoryids'];
                foreach ($categoryIds as $categoryId) {
                    $rewardCategoryModel = $this->rewardCategory->create()->load($categoryId, 'category_id');
                    if ($rewardCategoryModel->getEntityId()) {
                        $rewardPoint = $params['rewardpoint'];
                        if ($params['rewardpoint'] == '') {
                            $rewardPoint = $rewardCategoryModel->getPoints();
                        }
                        $data = [
                            'category_id' => $rewardCategoryModel->getCategoryId(),
                            'points' => $rewardPoint,
                            'status' => $params['status'],
                            'entity_id' => $rewardCategoryModel->getEntityId()
                        ];
                    } else {
                        $data = [
                            'category_id' => $categoryId,
                            'points' => $params['rewardpoint'],
                            'status' => $params['status']
                        ];
                    }
                    $this->helper->setCategoryRewardData($data);
                    $this->helper->clearCache();
                }
                if ($params['rewardpoint'] == '') {
                    $returnArray['message'] = __(
                        "Total of %1 category(s) reward status are updated",
                        count($categoryIds)
                    );
                } else {
                    $returnArray['message'] = __(
                        "Total of %1 category(s) reward are updated",
                        count($categoryIds)
                    );
                }
                $returnArray['status'] = self::SUCCESS;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please Enter a valid points number to add.')
                );
            }
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
