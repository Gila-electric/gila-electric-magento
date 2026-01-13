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
namespace Webkul\RewardSystem\Model\Config;

class CronConfig extends \Magento\Framework\App\Config\Value
{
    /**
     * @var CRON_STRING_PATH
     */
    public const CRON_STRING_PATH = 'crontab/default/jobs/webkul_rewards_cron/schedule/cron_expr';

    /**
     * @var CRON_MODEL_PATH
     */
    public const CRON_MODEL_PATH = 'crontab/default/jobs/webkul_rewards_cron/run/model';

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var \Webkul\RewardSystem\Logger\RewardLogger
     */
    protected $rewardLogger;

    /**
     * @var mixed|string
     */

    protected $_runModelPath = '';

    /**
     * CronConfig1 constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \Webkul\RewardSystem\Logger\RewardLogger $rewardLogger
     * @param string $runModelPath
     * @param array $data
     */

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Webkul\RewardSystem\Logger\RewardLogger $rewardLogger,
        $runModelPath = '',
        array $data = []
    ) {
        $this->_runModelPath = $runModelPath;
        $this->_configValueFactory = $configValueFactory;
        $this->rewardLogger = $rewardLogger;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Save Cron data.
     *
     * @return CronConfig
     * @throws \Exception
     */

    public function afterSave()
    {
        $time = $this->getData('groups/enable_cron_settings/fields/enable_time/value');
        $frequency = $this->getData('groups/enable_cron_settings/fields/enable_frequency/value');
        $cronExprArray = [
            (int) ($time[1]),
            (int) ($time[0]),
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY ? '1' : '*',
            '*',
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY ? '1' : '*',
        ];

        $cronExprString = join(' ', $cronExprArray);

        try {
            $this->_configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
            $this->_configValueFactory->create()->load(
                self::CRON_MODEL_PATH,
                'path'
            )->setValue(
                $this->_runModelPath
            )->setPath(
                self::CRON_MODEL_PATH
            )->save();
        } catch (\Exception $e) {
            $this->rewardLogger->info($e->getMessage());
        }
        return parent::afterSave();
    }
}
