<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

/** @var $product1 \Magento\Catalog\Model\Product */
$product1 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product1
    ->setTypeId('simple')
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('quickorder-simple-1')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->setUrlKey('quickorder-simple-1')
    ->save();

/** @var $product2 \Magento\Catalog\Model\Product */
$product2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product2
    ->setTypeId('simple')
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('quickorder-simple-2')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->setUrlKey('quickorder-simple-2')
    ->save();

/** @var $product3 \Magento\Catalog\Model\Product */
$product3 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\Product::class);
$product3
    ->setTypeId('simple')
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('quickorder-simple-3')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 0])
    ->setQty(22)
    ->setUrlKey('quickorder-simple-3')
    ->save();
