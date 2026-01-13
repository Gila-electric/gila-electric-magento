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
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * UpdateProductSpecificRewardPoint resolver, used for GraphQL request processing
 */
class UpdateProductSpecificRewardPoint implements ResolverInterface
{
    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var RewardproductInterfaceFactory
     */
    protected $rewardProduct;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

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
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param AdminRequestValidator $adminRequestValidator
     * @param \Webkul\RewardSystem\Helper\Data $helper
     */
    public function __construct(
        RewardproductInterfaceFactory $rewardProduct,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        AdminRequestValidator $adminRequestValidator,
        \Webkul\RewardSystem\Helper\Data $helper
    ) {
        $this->rewardProduct = $rewardProduct;
        $this->timezone = $timezone;
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
            $params['start_time'] = $this->converToTz(
                $params['start_time'] ?? "",
                // get default timezone of system (UTC)
                $this->timezone->getDefaultTimezone(),
                // get Config Timezone of current user
                $this->timezone->getConfigTimezone()
            );
            $params['end_time'] = $this->converToTz(
                $params['end_time'] ?? "",
                // get default timezone of system (UTC)
                $this->timezone->getDefaultTimezone(),
                // get Config Timezone of current user
                $this->timezone->getConfigTimezone()
            );
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
                        $startTime = $params['start_time'];
                        $endTime = $params['end_time'];
                        if ($params['rewardpoint'] == '') {
                            $rewardPoint = $rewardProductModel->getPoints();
                            $startTime = $rewardProductModel->getStartTime();
                            $endTime = $rewardProductModel->getEndTime();
                        }
                        $data = [
                            'product_id' => $rewardProductModel->getProductId(),
                            'points' => $rewardPoint,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                            'status' => $params['status'],
                            'entity_id' => $rewardProductModel->getEntityId()
                        ];
                    } else {
                        $data = [
                            'product_id' => $productId,
                            'points' => $params['rewardpoint'],
                            'start_time' => $params['start_time'],
                            'end_time' => $params['end_time'],
                            'status' => $params['status']
                        ];
                    }
                    $this->helper->setProductSpecificRewardData($data);
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

    /**
     * ConverToTz convert Datetime from one zone to another
     *
     * @param string $dateTime which we want to convert
     * @param string $toTz timezone in which we want to convert
     * @param string $fromTz timezone from which we want to convert
     */
    protected function converToTz($dateTime = "", $toTz = '', $fromTz = '')
    {
        // timezone by php friendly values
        $newDate = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $newDate->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $newDate->format('H:i');
        return $dateTime;
    }
}
