<?php
/**
 * Webkul DailyDeals Data Setup
 * @category  Webkul
 * @package   Webkul_DailyDeals
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\DailyDeals\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class CreateAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Apply
     *
     * @return void
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $statusSource = \Webkul\DailyDeals\Model\Config\Source\StatusOptions::class;
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_status',
            [
                'group' => 'Daily Deals',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Deal Status',
                'input' => 'select',
                'class' => '',
                'source' => $statusSource,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );
        $typeSource = \Webkul\DailyDeals\Model\Config\Source\DiscountOptions::class;
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_discount_type',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'group' => 'Daily Deals',
                'label' => 'Deal Type',
                'input' => 'select',
                'class' => '',
                'source' => $typeSource,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_discount_percentage',
            [
                'group' => 'Daily Deals',
                'label' => 'Deal Discount Percentage',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'input' => 'hidden',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_value',
            [
                'group'        => 'Daily Deals',
                'type'         => 'varchar',
                'backend'      => '',
                'frontend'     => '',
                'label'        => 'Deal Value',
                'input'        => 'text',
                'frontend_class' => 'required-entry validate-zero-or-greater',
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'      => true,
                'required'     => false,
                'user_defined' => true,
                'default'      => '',
                'searchable'   => false,
                'filterable'   => false,
                'comparable'   => false,
                'unique'       => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => true,
                'note' => 'In case of Percent type discount, the deal value      should not be 100 percent.
                           In case of fixed type discount, the deal value should not be more than product price.'
            ]
        );

         /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_from_date',
            [
                'group'        => 'Daily Deals',
                'type'         => 'datetime',
                'backend'      => '',
                'frontend'     => '',
                'input'        => 'hidden',
                'label'        => 'Deal From Date',
                'frontend_class' => '',
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'      => true,
                'required'     => false,
                'user_defined' => true,
                'default'      => '',
                'searchable'   => false,
                'filterable'   => false,
                'comparable'   => false,
                'unique'       => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true
            ]
        );

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_to_date',
            [
                'group'        => 'Daily Deals',
                'type'         => 'datetime',
                'label'        => 'Deal To Date',
                'backend'      => '',
                'frontend'     => '',
                'input'        => 'hidden',
                'frontend_class' => '',
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'      => true,
                'required'     => false,
                'user_defined' => true,
                'default'      => '',
                'searchable'   => false,
                'filterable'   => false,
                'comparable'   => false,
                'unique'       => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => true
            ]
        );
    }

    /**
     * Get Dependencies
     *
     * @return void
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get Aliases
     *
     * @return void
     */
    public function getAliases()
    {
        return [];
    }
}
