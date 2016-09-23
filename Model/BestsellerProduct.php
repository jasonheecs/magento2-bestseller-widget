<?php

/**
 * Product model for bestseller widget
 * NOTE to self: This model extends the Magento native product model to provide a more customised API for the widget;
 * But extending the product model seems like a bad idea (dependency injection via constructor will be troublesome).
 * Maybe I should decouple the custom API from the native model? 
 * Perhaps I can have an internal class variable to keep a reference to the product model.
 * Anyways, this works for now.
 */

namespace Jason\Bestseller\Model;

use Magento\Framework\App\ObjectManager;

class BestsellerProduct extends \Magento\Catalog\Model\Product
{
    protected $block;

    /**
     * Sets the widget template block to get access to helper functions
     * @param \Magento\Framework\View\Element\Template $block
     */
    public function setBlock($block)
    {
        $this->block = $block;
    }

    /**
     * Helper function to get the right template block, depending on if argument is null
     * @param  \Magento\Framework\View\Element\Template $block
     * @return \Magento\Framework\View\Element\Template
     */
    protected function getBlock($block)
    {
        return $block !== null ?: $this->block;
    }

    /**
     * Gets the product image url to be displayed in the widget
     * @return string
     */
    public function getImageHTML(\Magento\Framework\View\Element\Template $block = null)
    {
        return $this->getBlock($block)->getImage($this, 'new_products_content_widget_grid')->toHtml();
    }

    /**
     * Checks if the product has some required options before user can add it to cart
     * @return bool
     */
    public function hasRequiredOptions()
    {
        return $this->getTypeInstance()->hasRequiredOptions($this);
    }

    /**
     * Get add to cart post data for the product
     * @param  \Magento\Catalog\Block\Product\AbstractProduct $block
     * @return string
     */
    public function getCartPostData(
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Block\Product\AbstractProduct $block = null
    ) {
        return $postDataHelper->getPostData(
            $this->getBlock($block)->getAddToCartUrl($this),
            ['product' => $this->getEntityId()]
        );
    }
}
