<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_RewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\RewardSystem\Model\ResourceModel\ReferralDetail;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Webkul\RewardSystem\Model\ReferralDetail::class,
            \Webkul\RewardSystem\Model\ResourceModel\ReferralDetail::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}
