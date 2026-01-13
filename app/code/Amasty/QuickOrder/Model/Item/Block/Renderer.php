<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Quick Order for Magento 2
 */

namespace Amasty\QuickOrder\Model\Item\Block;

use Amasty\QuickOrder\Model\Item\Block\Renderer\Flag as RendererFlag;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\ConfiguredPrice;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout\BuilderFactory as LayoutBuilderFactory;
use Magento\Framework\View\Layout\GeneratorPool;
use Magento\Framework\View\Layout\ReaderPool;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class Renderer
{
    public const OPTIONS_WRAPPER_NAME = 'product.info.options.wrapper';
    public const GROUPED_WRAPPER_NAME = 'product.info.grouped';
    public const GIFT_CARD_OPTIONS = 'product.info.giftcard';
    public const GIFT_CARD_PRICE_OPTION = 'product.price.final';

    public const PRICE_RENDERER = 'product.price.render.default';

    public const ADDITIONAL_CONFIGS = 'additional.configs';

    /**
     * @var Layout[]
     */
    private $layoutPool;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ReaderPool
     */
    private $layoutReaderPool;

    /**
     * @var GeneratorPool
     */
    private $layoutGeneratorPool;

    /**
     * @var LayoutBuilderFactory
     */
    private $layoutBuilderFactory;

    /**
     * @var RendererFlag
     */
    private $rendererFlag;

    public function __construct(
        LayoutFactory $layoutFactory,
        Registry $registry,
        RendererFlag $rendererFlag,
        ReaderPool $layoutReaderPool,
        GeneratorPool $layoutGeneratorPool,
        LayoutBuilderFactory $layoutBuilderFactory
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->registry = $registry;
        $this->layoutReaderPool = $layoutReaderPool;
        $this->layoutGeneratorPool = $layoutGeneratorPool;
        $this->layoutBuilderFactory = $layoutBuilderFactory;
        $this->rendererFlag = $rendererFlag;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product)
    {
        $this->registry->unregister('current_product');
        $this->registry->unregister('product');
        $this->registry->register('current_product', $product);
        $this->registry->register('product', $product);
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->registry->registry('product');
    }

    public function getOptionsHtml(int $itemId): string
    {
        $html = '';
        $typeId = $this->getProduct()->getTypeId();

        $layout = $this->getLayout($typeId)->setProduct($this->getProduct());

        if ($block = $layout->getBlock($this->getOptionBlockName($typeId))) {
            $this->rendererFlag->enable();
            if ($this->getProduct()->getTypeId() === 'giftcard') {
                $html .= $this->getGiftCardOptions();
            }
            $html .= $block->toHtml();
            $this->rendererFlag->disable();
        }

        return $this->adaptOptionsForList($html, $itemId);
    }

    private function getGiftCardOptions(): string
    {
        $result = '';

        $layout = $this->getLayout($this->getProduct()->getTypeId());
        $amountBlock = $layout->getBlock(static::GIFT_CARD_PRICE_OPTION);
        $optionsBlock = $layout->getBlock(static::GIFT_CARD_OPTIONS);
        if ($amountBlock && $optionsBlock) {
            $amountBlock->setCacheLifetime(false);
            $amountBlock->setPriceTypeCode('final_price');

            $result .= $amountBlock->toHtml() . $optionsBlock->toHtml();
        }

        return $result;
    }

    private function getOptionBlockName(string $typeId): string
    {
        switch ($typeId) {
            case Grouped::TYPE_CODE:
                $blockName = static::GROUPED_WRAPPER_NAME;
                break;
            default:
                $blockName = static::OPTIONS_WRAPPER_NAME;
        }

        return $blockName;
    }

    /**
     * @param int $itemId
     * @return string
     */
    public function getPriceHtml(int $itemId): string
    {
        $html = '';

        $layout = $this->getLayout($this->getProduct()->getTypeId());

        if ($priceRender = $layout->getBlock(static::PRICE_RENDERER)) {
            $html .= $priceRender->render(
                $this->getPriceTypeCode($this->getProduct()->getTypeId()),
                $this->getProduct(),
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }

        if ($additionalConfigs = $layout->getBlock(static::ADDITIONAL_CONFIGS)) {
            foreach ($additionalConfigs->getChildNames() as $configBlockName) {
                /** @var \Amasty\QuickOrder\Block\Script\ConfigInterface $configBlock */
                $configBlock = $additionalConfigs->getChildBlock($configBlockName);
                $configBlock->setItemId($itemId);
                $html .= $configBlock->toHtml();
            }
        }

        return $this->adaptPriceForList($html, $itemId);
    }

    /**
     * Create layout with specified handles for each product type.
     * Use custom layout \Amasty\QuickOrder\Model\Item\Block\Layout for avoid cache,
     * because one layout used for render options for many products.
     *
     * @param string $typeId
     * @return Layout
     */
    public function getLayout(string $typeId): Layout
    {
        if (!isset($this->layoutPool[$typeId])) {
            $layout = $this->layoutFactory->create([
                'reader' => $this->layoutReaderPool,
                'generatorPool' => $this->layoutGeneratorPool
            ]);

            $this->layoutBuilderFactory->create(LayoutBuilderFactory::TYPE_PAGE, ['layout' => $layout]);

            $layout->getUpdate()
                ->addHandle('default')
                ->addHandle('catalog_product_view')
                ->addHandle('catalog_product_view_type_' . $typeId)
                ->addHandle('amasty_quickorder_catalog_product_view_type_' . $typeId)
                ->addHandle('amasty_quickorder_grid');

            $this->layoutPool[$typeId] = $layout;
        }

        return $this->layoutPool[$typeId];
    }

    /**
     * Adapt options html for list view. Replace same identifiers with unique.
     *
     * @param string $html
     * @param int $itemId
     * @return string
     */
    private function adaptOptionsForList(string $html, int $itemId): string
    {
        $itemSelector = sprintf('[data-amqorder-js=\"item\"][data-item-id=\"%d\"]', $itemId);
        $priceHolderSelector = sprintf('%s [data-role=priceBox]', $itemSelector);

        // replace for unique selector for customOptions / configurableDropdowns
        $html = str_replace(
            '#product_addtocart_form',
            $itemSelector,
            $html
        );

        // replace priceHolderSelector for custom options . needed for find correctly price box
        $html = preg_replace(
            '@"priceHolderSelector":\s*"[^"]*"@s',
            sprintf('"priceHolderSelector":"%s"', $priceHolderSelector),
            $html
        );

        // replace for swatches. need for correctly work on list
        $html = preg_replace(
            '@data-role=("?)swatch-options("?)@s',
            sprintf('data-role=$1swatch-option-%d$2', $itemId),
            $html
        );
        // replace for swatches. need for ajax image reload
        $html = str_replace(
            '"images":{',
            '"fake-images":{',
            $html
        );
        $html = str_replace(
            '"jsonConfig":',
            '"selectorProduct":"[data-amqorder-js=item]","jsonConfig":',
            $html
        );

        // replace for configurable swatches/dropdowns.
        // need for "As low as" label reload only for current changed product
        $html = preg_replace(
            '@"(jsonConfig|spConfig)":@s',
            sprintf(
                '"normalPriceLabelSelector":"%s .normal-price .price-label","$1":',
                $itemSelector
            ),
            $html
        );
        $html = str_replace(
            '"jsonConfig":',
            sprintf(
                '"classes":{"attributeOptionsWrapper":"swatch-attribute-options-%d"},"jsonConfig":',
                $itemId
            ),
            $html
        );

        // replace for configurable dropdowns. need for correctly work on list
        $html = str_replace(
            '"spConfig": {"attributes',
            sprintf('"priceHolderSelector":"%s", "spConfig": {"attributes', $priceHolderSelector),
            $html
        );

        $html = str_replace(
            'spConfig": {"attributes',
            sprintf('spConfig": {"containerId":"%s", "attributes', $itemSelector),
            $html
        );

        // replace for bundles
        $html = preg_replace(
            '@name="bundle_option\[(\d+)\]@s',
            sprintf('name="bundle_option[$1][%d]', $itemId),
            $html
        );
        $html = preg_replace(
            '@data-selector="bundle_option\[(\d+)\]@s',
            sprintf('data-selector="bundle_option[$1][%d]', $itemId),
            $html
        );
        $html = preg_replace(
            '@"bundle-option-(\d+)-(\d+)@s',
            sprintf('"bundle-option-%d-$1-$2', $itemId),
            $html
        );
        $html = preg_replace(
            '@"controlContainer":\s*"[^"]*"@s',
            sprintf('"controlContainer":"%s"', ".amqorder-option, .field.option"),
            $html
        );

        // replace for downloadables
        $html = preg_replace(
            '@"links_(\d+)@s',
            sprintf('"links_%d_$1', $itemId),
            $html
        );
        $html = preg_replace(
            '@"downloadable":\{[\n\s]*"linkElement@s',
            sprintf('"downloadable":{"priceHolderSelector":"%s", "linkElement', $priceHolderSelector),
            $html
        );
        $html = str_replace(
            'links_all',
            sprintf('links_all-%s', $itemId),
            $html
        );
        $html = str_replace(
            'input type="checkbox"',
            sprintf('input type="checkbox" data-quickorder-item="%s"', $itemId),
            $html
        );
        $html = str_replace(
            'input:checkbox[value]',
            sprintf('input[data-quickorder-item=\"%s\"]:checkbox[value]', $itemId),
            $html
        );

        // replace for gift cards
        $html = str_replace(
            '"toggleGiftCard":{',
            sprintf(
                '"toggleGiftCard":{"amountSelector":"%1$s #giftcard-amount-input",
                "amountBoxSelector":"%1$s #giftcard-amount-box",',
                $itemSelector
            ),
            $html
        );

        return $html;
    }

    protected function adaptPriceForList(string $html, int $itemId): string
    {
        // replace for grouped
        $html = preg_replace(
            '@data-price-box="product-id-\d+"@s',
            sprintf('data-price-box="product-id-%d"', $itemId),
            $html
        );

        return $html;
    }

    /**
     * @param string $typeId
     * @return string
     */
    protected function getPriceTypeCode(string $typeId): string
    {
        switch ($typeId) {
            case Bundle::TYPE_CODE:
            case Grouped::TYPE_CODE:
                $priceCode = ConfiguredPrice::CONFIGURED_PRICE_CODE;
                break;
            case 'giftcard':
                $priceCode = 'quickorder_subtotal';
                break;
            default:
                $priceCode = FinalPrice::PRICE_CODE;
        }

        return $priceCode;
    }
}
