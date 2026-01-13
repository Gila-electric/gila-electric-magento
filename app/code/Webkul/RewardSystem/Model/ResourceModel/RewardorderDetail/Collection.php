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

namespace Webkul\RewardSystem\Model\ResourceModel\RewardorderDetail;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Webkul\RewardSystem\Model\RewardorderDetail::class,
            \Webkul\RewardSystem\Model\ResourceModel\RewardorderDetail::class
        );
    }
}
