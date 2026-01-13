<?php

namespace Webkul\DailyDeals\Plugin\Currency;

class SwitchAction
{
    /**
     * @var \Webkul\DailyDeals\Helper\Data
     */
    private $helper;
    
    /**
     * Constructor
     *
     * @param \Webkul\DailyDeals\Helper\Data $helper
     */
    public function __construct(
        \Webkul\DailyDeals\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    /**
     * After Execute
     *
     * @param \Magento\Directory\Controller\Currency\SwitchAction $subject
     * @param string $result
     * @return void
     */
    public function afterExecute(
        \Magento\Directory\Controller\Currency\SwitchAction $subject,
        $result
    ) {
        $this->helper->updateItemsOnCurrencyChange();
        return $result;
    }
}
