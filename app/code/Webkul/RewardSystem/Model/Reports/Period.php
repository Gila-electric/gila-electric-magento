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
declare(strict_types=1);

namespace Webkul\RewardSystem\Model\Reports;

/**
 * Reports period info retriever
 */
class Period
{
    public const PERIOD_TODAY = 'today';
    public const PERIOD_7_DAYS = '7d';
    public const PERIOD_30_DAYS = '30d';
    public const PERIOD_6_MONTHS = '1y';
    public const PERIOD_2_YEARS = '2y';
    public const PERIOD_OVERALL = 'all';

    private const PERIOD_UNIT_DAY = 'day';
    private const PERIOD_UNIT_MONTH = 'month';
    private const PERIOD_UNIT_YEAR = 'year';

    /**
     * Prepare array with periods for reports graphs
     *
     * @return array
     */
    public function getDatePeriods(): array
    {
        return [
            static::PERIOD_TODAY => __('Today'),
            static::PERIOD_7_DAYS => __('Last 7 Days'),
            static::PERIOD_30_DAYS => __('Last 30 Days'),
            static::PERIOD_6_MONTHS => __('Last 6 Months'),
            static::PERIOD_2_YEARS => __('Last 2 Years'),
            static::PERIOD_OVERALL => __('Overall')
        ];
    }

    /**
     * Prepare array with periods mapping to chart units
     *
     * @return array
     */
    public function getPeriodChartUnits(): array
    {
        return [
            static::PERIOD_TODAY => self::PERIOD_UNIT_DAY,
            static::PERIOD_7_DAYS => self::PERIOD_UNIT_DAY,
            static::PERIOD_30_DAYS => self::PERIOD_UNIT_DAY,
            static::PERIOD_6_MONTHS => self::PERIOD_UNIT_MONTH,
            static::PERIOD_2_YEARS => self::PERIOD_UNIT_YEAR,
            static::PERIOD_OVERALL => self::PERIOD_UNIT_YEAR
        ];
    }

    /**
     * Prepare array with periods mapping to number of units
     *
     * @return array
     */
    public function getPeriodChartUnitsMaxNumber(): array
    {
        return [
            static::PERIOD_TODAY => 1,
            static::PERIOD_7_DAYS => 7,
            static::PERIOD_30_DAYS => 30,
            static::PERIOD_6_MONTHS => 6,
            static::PERIOD_2_YEARS => 2,
            static::PERIOD_OVERALL => 10
        ];
    }
}
