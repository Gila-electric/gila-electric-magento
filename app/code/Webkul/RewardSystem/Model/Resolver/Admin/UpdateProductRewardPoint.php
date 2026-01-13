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
use Webkul\RewardSystem\Api\Data\RewardproductInterfaceFactory;

/**
 * UpdateProductRewardPoint resolver, used for GraphQL request processing
 */
class UpdateProductRewardPoint implements ResolverInterface
{
    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var RewardproductInterfaceFactory
     */
    protected $rewardProduct;

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
     * @param RewardproductInterfaceFactory $rewardProduct
     * @param AdminRequestValidator $adminRequestValidator
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        RewardproductInterfaceFactory $rewardProduct,
        AdminRequestValidator $adminRequestValidator,
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->rewardProduct = $rewardProduct;
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

            if (empty($params['wk_productids']) || !is_array($params['wk_productids'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please select products to set points.')
                );
            }
            if (array_key_exists('rewardpoint', $params) &&
                array_key_exists('status', $params)) {
                $productIds = $params['wk_productids'];
                foreach ($productIds as $productId) {
                    $rewardProductModel = $this->rewardProduct->create()->load($productId, 'product_id');
                    if ($rewardProductModel->getEntityId()) {
                        $rewardPoint = $params['rewardpoint'];
                        if ($params['rewardpoint'] == '') {
                            $rewardPoint = $rewardProductModel->getPoints();
                        }
                        $data = [
                            'product_id' => $rewardProductModel->getProductId(),
                            'points' => $rewardPoint,
                            'status' => $params['status'],
                            'entity_id' => $rewardProductModel->getEntityId()
                        ];
                    } else {
                        $data = [
                            'product_id' => $productId,
                            'points' => $params['rewardpoint'],
                            'status' => $params['status']
                        ];
                    }
                    $this->helper->setProductRewardData($data);
                    $this->helper->clearCache();
                }
                if ($params['rewardpoint'] == '') {
                    $returnArray['message'] = __(
                        "Total of %1 product(s) reward status are updated",
                        count($productIds)
                    );
                } else {
                    $returnArray['message'] = __(
                        "Total of %1 product(s) reward are updated",
                        count($productIds)
                    );
                }
                $returnArray['status'] = self::SUCCESS;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please Enter a valid reward points number to add.')
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
