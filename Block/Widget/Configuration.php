<?php

/**
 * Configuration class for Bestseller widget. 
 * This class is used to handle user input values for configuration,
 * and to set certain values to predefined defaults.
 */

namespace Jason\Bestseller\Block\Widget;

class Configuration
{
    /**
     * Default configuration values
     */
    const DEFAULT_PRODUCTS_COUNT = 10;
    const DEFAULT_TITLE = 'Bestsellers';
    const DEFAULT_SUBTITLE = '';

    /**
     * @var array
     */
    protected $configData;

    public function __construct()
    {
    }

    /**
     * Set the configuration data
     * @param array $data
     */
    public function setData($data)
    {
        $this->configData = $data;
    }

    /**
     * Get the limit count of products to be displayed
     * @return int
     */
    public function getProductsCount()
    {
        return $this->getValue('products_count');
    }

    /**
     * Get the title of the widget
     * @return string
     */
    public function getTitle()
    {
        return $this->getValue('title');
    }

    /**
     * Get the subtitle of the widget
     * @return string
     */
    public function getSubtitle()
    {
        return $this->getValue('subtitle');
    }

    /**
     * Get the list of dummy product ids (separated by commas)
     * @return array
     */
    public function getDummyProductsIds()
    {
        if (isset($this->configData['dummy_products_ids'])) {
            return explode(',', $this->configData['dummy_products_ids']);
        }

        return [];
    }

    /**
     * Checks if widget is using a carousel to display the products
     * @return bool
     */
    public function useCarousel()
    {
        return isset($this->configData['use_carousel']) && $this->configData['use_carousel'] == 1;
    }

    /**
     * Get a value from the configuration values
     * If value does not exist in the configuration, use the default.
     * @param  string $key
     * @return mixed
     */
    protected function getValue($key)
    {
        return isset($this->configData[$key]) ?
               $this->configData[$key] : eval('return self::DEFAULT_' . strtoupper($key) . ';');
    }
}
