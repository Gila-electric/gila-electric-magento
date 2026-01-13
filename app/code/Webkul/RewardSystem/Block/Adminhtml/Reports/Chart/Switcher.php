<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RewardSystem\Block\Adminhtml\Reports\Chart;

use Magento\Framework\App\ObjectManager;
use Webkul\RewardSystem\Model\Reports\Period;

class Switcher extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_RewardSystem::reports/chart/switcher.phtml';

    /**
     * @var \Webkul\RewardSystem\Helper\Data
     */
    protected $rewardHelper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     */
    protected $websiteCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $customerGroupCollection;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var Period
     */
    protected $period;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\RewardSystem\Helper\Data $rewardHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupCollection
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param Period|null $period
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\RewardSystem\Helper\Data $rewardHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupCollection,
        \Magento\Store\Model\System\Store $systemStore,
        Period $period = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->rewardHelper = $rewardHelper;
        $this->customerFactory = $customerFactory;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->customerGroupCollection = $customerGroupCollection;
        $this->systemStore = $systemStore;
        $this->period = $period ?? ObjectManager::getInstance()->get(Period::class);
    }

    /**
     * Function getWebsiteOptions
     *
     * @return array
     */
    public function getWebsiteCodeOptions()
    {
        $options = [];
        $collection = $this->websiteCollectionFactory->create();
        foreach ($collection as $website) {
            $data['value'] = $website->getCode();
            $data['label'] = $website->getName();
            $options[] = $data;
        }
        return $options;
    }

    /**
     * Function getWebsiteOptions
     *
     * @return array
     */
    public function getWebsiteOptions()
    {
        $defaultOption[] = [
            'value' => 'all',
            'label' => __('All Websites')
        ];
        $options = $this->systemStore->getWebsiteValuesForForm(false, false);
        $allOptions = array_merge($defaultOption, $options);

        return $allOptions;
    }

    /**
     * Function getCustomerGroupOptions
     *
     * @return array
     */
    public function getCustomerGroupOptions()
    {
        $defaultOption[] = [
            'value' => 'all',
            'label' => __('All Customer Groups')
        ];
        $options = $this->customerGroupCollection->toOptionArray();
        unset($options[0]);
        $allOptions = array_merge($defaultOption, $options);

        return $allOptions;
    }

    /**
     * Function getWebsiteOptions
     *
     * @return array
     */
    public function getDatePeriodOptions()
    {
        return $this->period->getDatePeriods();
    }

    /**
     * Get Helper Class
     */
    public function getRewardHelper()
    {
        return $this->rewardHelper;
    }
}
