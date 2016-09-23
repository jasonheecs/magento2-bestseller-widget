<?php

/**
 * Bestseller products widget for Magento 2
 *
 * @version 1.0
 * @author Jason Hee <https://jasonhee.com>
 * @link https://github.com/jasonheecs/magento2-bestseller-widget.git
 */

namespace Jason\Bestseller\Block\Widget;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Widget\Block\BlockInterface;
use Jason\Bestseller\Model\BestsellerProduct;

class Bestseller extends \Magento\Catalog\Block\Product\AbstractProduct implements BlockInterface, IdentityInterface
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Jason\Bestseller\Model\BestsellerProductFactory
     */
    protected $bestsellerProductFactory;

    /**
     * List of products in bestsellers report
     * @var \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory
     */
    protected $bestsellersCollection;

    /**
     * Widget Configuration helper
     * @var \Jason\Bestseller\Block\Widget\Configuration
     */
    protected static $configuration;

    /**
     * @var array
     */
    protected $products;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder
     * @param \Magento\CatalogWidget\Model\Rule $rule
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory $collectionFactory,
        \Jason\Bestseller\Model\BestsellerProductFactory $bestsellerProductFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        Configuration $configuration,
        array $data = []
    ) {
        $this->bestsellersCollection = $collectionFactory;
        $this->bestsellerProductFactory = $bestsellerProductFactory;
        $this->httpContext = $httpContext;
        self::$configuration = $configuration;

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Get configuration values for widget
     * @return \Jason\Bestseller\Block\Widget\Configuration
     */
    public function getConfiguration()
    {
        if (self::$configuration === null) {
            throw new \LogicException('Configuration not initialized!');
        }

        return self::$configuration;
    }

    /**
     * Check if the store actually has any bestselling products
     * @return bool
     */
    public function hasBestsellerProducts()
    {
        return $this->getProducts() && !empty($this->getProducts());
    }

    /**
     * Get best selling products
     * @return array
     */
    public function getProducts()
    {
        if (!isset($this->products) || empty($this->products)) {
            if ($this->hasDummyProducts()) {
                $this->products = $this->getDummyProducts();
            } else {
                $collection = $this->getBestsellerCollection()->getItems();

                $products = [];
                foreach ($collection as $resource) {
                    $products[] = $this->getProductById($resource->getProductId());
                }
                $this->products = $products;
            }
        }

        return $this->products;
    }

    /**
     * Checks if widget is using dummy products instead of actual bestsellers
     * @return bool
     */
    public function hasDummyProducts()
    {
        $dummyProductsIds = $this->getConfiguration()->getDummyProductsIds();
        return !empty($dummyProductsIds);
    }

    /**
     * Gets list of dummy products
     * @return array
     */
    protected function getDummyProducts()
    {
        $dummyIds = $this->getConfiguration()->getDummyProductsIds();
        $products = [];

        foreach ($dummyIds as $id) {
            $products[] = $this->getProductById($id);
        }

        return $products;
    }

    /**
     * Get collection of best selling products
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getBestSellerCollection()
    {
        $collection = $this->bestsellersCollection->create()->setModel(
            'Magento\Catalog\Model\Product'
        );
        $collection->setPageSize($this->getConfiguration()->getProductsCount());

        return $collection;
    }

    /**
     * Get an instance of a product based on it's id
     * @param  int $id product id
     * @return \Jason\Bestseller\Block\Widget\BestsellerProduct
     */
    public function getProductById($id)
    {
        $product = $this->bestsellerProductFactory->create()->load($id);
        $product->setBlock($this);

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        self::$configuration->setData($this->getData());
        return parent::_beforeToHtml();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getProductCollection()) {
            foreach ($this->getProductCollection() as $product) {
                if ($product instanceof IdentityInterface) {
                    $identities = array_merge($identities, $product->getIdentities());
                }
            }
        }

        return $identities ?: [\Magento\Catalog\Model\Product::CACHE_TAG];
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
            'JASON_BESTSELLER_WIDGET',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            intval($this->getRequest()->getParam($this->getData('page_var_name'), 1)),
            $this->getConfiguration()->getProductsCount(),
            serialize($this->getRequest()->getParams())
        ];
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

            /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }
        return $price;
    }
}
