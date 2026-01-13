<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RewardSystem
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RewardSystem\Ui\Component;

use Magento\Framework\View\Element\UiComponentInterface;

class ExportButton extends \Magento\Ui\Component\AbstractComponent
{
    /**
     * Component name
     */
    public const NAME = 'exportButton';

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    
    /**
     * Render context
     *
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var UiComponentInterface[]
     */
    protected $components;

    /**
     * @var array
     */
    protected $componentData = [];

    /**
     * @var DataSourceInterface[]
     */
    protected $dataSources = [];

    /**
     * @param ContextInterface $context
     * @param UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Request\Http $request,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->_urlBuilder = $urlBuilder;
        $this->_request = $request;
    }

    /**
     * Rrepare method
     *
     * @return void
     */
    public function prepare()
    {
        $customerId = $this->_request->getParam('customer_id');
        if (isset($customerId)) {
            $configData = $this->getData('config');
            if (isset($configData['options'])) {
                $configOptions = [];
                foreach ($configData['options'] as $configOption) {
                    $configOption['url'] = $this->_urlBuilder->getUrl(
                        $configOption['url'],
                        ["customer_id"=>$customerId]
                    );
                    $configOptions[] = $configOption;
                }
                $configData['options'] = $configOptions;
                $this->setData('config', $configData);
            }
        }
        parent::prepare();
    }

    /**
     * Get component context
     *
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('name');
    }

     /**
      * Call prepare method in the component UI
      *
      * @param UiComponentInterface $component
      * @return $this
      * @since 100.1.0
      */
    protected function prepareChildComponent(UiComponentInterface $component)
    {
        $childComponents = $component->getChildComponents();
        if (!empty($childComponents)) {
            foreach ($childComponents as $child) {
                $this->prepareChildComponent($child);
            }
        }
        $component->prepare();

        return $this;
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml()
    {
        $this->render();
    }

    /**
     * Render component
     *
     * @return string
     */
    public function render()
    {
        $result = $this->getContext()->getRenderEngine()->render($this, $this->getTemplate());
        return $result;
    }

    /**
     * Add component
     *
     * @param string $name1
     * @param UiComponentInterface $component
     * @return void
     */
    public function addComponent($name1, UiComponentInterface $component)
    {
        $this->components[$name1] = $component;
    }

    /**
     * Get Component
     *
     * @param string $name1
     * @return UiComponentInterface
     */
    public function getComponent($name1)
    {
        return isset($this->components[$name1]) ? $this->components[$name1] : null;
    }

    /**
     * Get components
     *
     * @return UiComponentInterface[]
     */
    public function getChildComponents()
    {
        return $this->components;
    }

    /**
     * Render child component
     *
     * @param string $name1
     * @return string
     */
    public function renderChildComponent($name1)
    {
        $result = null;
        if (isset($this->components[$name1])) {
            $result = $this->components[$name1]->render();
        }
        return $result;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->getData('template') . '.xhtml';
    }

    /**
     * Get component configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        return (array)$this->getData('config');
    }

    /**
     * Get configuration of related JavaScript Component
     * (force extending the root component if component does not extend other component)
     *
     * @param UiComponentInterface $component
     * @return array
     */
    public function getJsConfig(UiComponentInterface $component)
    {
        $jsConfig = (array)$component->getData('js_config');
        if (!isset($jsConfig['extends'])) {
            $jsConfig['extends'] = $component->getContext()->getNamespace();
        }
        return $jsConfig;
    }

    /**
     * Set Data
     *
     * @param string|array $key
     * @param mixed $value1
     * @return void
     */
    public function setData($key, $value1 = null)
    {
        $doNothing = true;
        parent::setData($key, $value1);
    }

    /**
     * Component data getter
     *
     * @param string $key
     * @param string|int $index1
     * @return mixed
     */
    public function getData($key = '', $index1 = null)
    {
        $doNothing = true;
        return parent::getData($key, $index1);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        return $dataSource;
    }

    /**
     * Get Data Source Data
     */
    public function getDataSourceData()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getComponentName() : string
    {
        return static::NAME;
    }

    /**
     * Initiate observers
     *
     * @param array $data
     * @return void
     */
    protected function initObservers(array &$data = [])
    {
        if (isset($data['observers']) && is_array($data['observers'])) {
            foreach ($data['observers'] as $observerType => $observer) {
                if (!is_object($observer)) {
                    $observer = $this;
                }
                if ($observer instanceof ObserverInterface) {
                    $this->getContext()->getProcessor()->attach($observerType, $observer);
                }
                unset($data['observers']);
            }
        }
    }
}
