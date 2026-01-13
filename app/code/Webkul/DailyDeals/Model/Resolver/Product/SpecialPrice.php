<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
declare(strict_types=1);

namespace Webkul\DailyDeals\Model\Resolver\Product;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\Exception\LocalizedException;

class SpecialPrice implements ResolverInterface
{
    /**
     * @var  \Webkul\DailyDeals\Helper\Data
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
     * Resolver
     *
     * @param Field $field
     * @param Context $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return string
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $product = $value['model'];
        $fieldName = $field->getName();
        $price = $product->getSpecialPrice();
        if (!$product->getSpecialPrice()) {
            $this->helper->updateProductDealDetail($product, $value);
            list($price,$discount) = $this->helper->getDealValue($product, $value);
          
        }
        return $price;
    }
}
