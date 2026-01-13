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
declare(strict_types=1);

namespace Webkul\RewardSystem\Model\Resolver\Checkout;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

use Magento\Framework\Session\SessionManager;
use Webkul\RewardSystem\Model\RewardrecordFactory as RewardRecordCollection;
use Webkul\RewardSystem\Helper\Data as RewardHelper;

/**
 * @inheritdoc
 */
class CancelRewards implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;
    
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var RewardRecordCollection;
     */
    protected $rewardRecordCollection;

    /**
     * @var RewardHelper;
     */
    protected $helper;

    /**
     * @param GetCartForUser $getCartForUser
     * @param SessionManager $session
     * @param RewardRecordCollection $rewardRecordCollection
     * @param RewardHelper $helper
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        SessionManager $session,
        RewardRecordCollection $rewardRecordCollection,
        RewardHelper $helper
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->session = $session;
        $this->rewardRecordCollection = $rewardRecordCollection;
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
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];

        $customerId = $context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        $quote = $this->getCartForUser->execute($maskedCartId, $customerId, $storeId);

        $this->helper->unsetRewardInfoInQuote($quote);
        return [
            'cart' => [
                'model' => $quote,
            ],
        ];
    }
}
