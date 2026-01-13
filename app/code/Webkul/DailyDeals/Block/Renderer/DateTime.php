<?php
/**
 * Webkul_DailyDeals Product form.
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\DailyDeals\Block\Renderer;

class DateTime extends \Magento\Framework\Data\Form\Element\Date
{

    /**
     * @var \Webkul\DailyDeals\Helper\Data
     */
    private $dailyDealHelper;

    /**
     * Constructor
     *
     * @param \Webkul\DailyDeals\Helper\Data $dailyDealHelper
     */
    public function __construct(
        \Webkul\DailyDeals\Helper\Data $dailyDealHelper
    ) {
        $this->dailyDealHelper = $dailyDealHelper;
    }

    /**
     * Output the input field and assign calendar instance to it.
     * In order to output the date:
     * - the value must be instantiated (\DateTime)
     * - output format must be set (compatible with \DateTime)
     *
     * @throws \Exception
     * @return string
     */
    public function getElementHtml()
    {
        $this->addClass('admin__control-text  input-text');
        $dateFormat = $this->getDateFormat() ?: $this->getFormat();
        $timeFormat = $this->getTimeFormat();
        if (empty($dateFormat)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                'Output format is not specified. ' .
                'Please specify "format" key in constructor, or set it using setFormat().'
            );
        }

        $dataInit = 'data-mage-init="' . $this->_escape(
            $this->dailyDealHelper->jsonEncode(
                [
                    'calendar' => [
                        'dateFormat' => $dateFormat,
                        'showsTime' => true,
                        'timeFormat' => 'HH:mm:ss',
                        'buttonImage' => $this->getImage(),
                        'buttonText' => 'Select Date',
                        'disabled' => $this->getDisabled(),
                        'minDate' => $this->getMinDate(),
                        'maxDate' => $this->getMaxDate(),
                    ],
                ]
            )
        ) . '"';

        $html = sprintf(
            '<input name="%s" id="%s" value="%s" %s %s />',
            $this->getName(),
            $this->getHtmlId(),
            $this->_escape($this->getValue()),
            $this->serialize($this->getHtmlAttributes()),
            $dataInit
        );
        $html .= $this->getAfterElementHtml();
        return $html;
    }
}
