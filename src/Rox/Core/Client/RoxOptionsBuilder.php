<?php

namespace Rox\Core\Client;

class RoxOptionsBuilder
{
    /**
     * @var string $_devModeKey
     */
    private $_devModeKey;

    /**
     * @var string $_version
     */
    private $_version;

    /**
     * @var callable|null
     */
    private $_impressionHandler;

    /**
     * @var callable|null $_configurationFetchedHandler
     */
    private $_configurationFetchedHandler;

    /**
     * @var string|null $_roxyURL
     */
    private $_roxyURL;

    /**
     * @var callable|null $_dynamicPropertiesRule
     */
    private $_dynamicPropertiesRule;

    /**
     * @return string
     */
    public function getDevModeKey()
    {
        return $this->_devModeKey;
    }

    /**
     * @param string $devModeKey
     * @return RoxOptionsBuilder
     */
    public function setDevModeKey($devModeKey)
    {
        $this->_devModeKey = $devModeKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * @param string $version
     * @return RoxOptionsBuilder
     */
    public function setVersion($version)
    {
        $this->_version = $version;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getImpressionHandler()
    {
        return $this->_impressionHandler;
    }

    /**
     * @param callable|null $impressionHandler
     * @return RoxOptionsBuilder
     */
    public function setImpressionHandler($impressionHandler)
    {
        $this->_impressionHandler = $impressionHandler;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getConfigurationFetchedHandler()
    {
        return $this->_configurationFetchedHandler;
    }

    /**
     * @param callable|null $configurationFetchedHandler
     * @return RoxOptionsBuilder
     */
    public function setConfigurationFetchedHandler($configurationFetchedHandler)
    {
        $this->_configurationFetchedHandler = $configurationFetchedHandler;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRoxyURL()
    {
        return $this->_roxyURL;
    }

    /**
     * @param string $roxyURL
     * @return RoxOptionsBuilder
     */
    public function setRoxyURL($roxyURL)
    {
        $this->_roxyURL = $roxyURL;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getDynamicPropertiesRule()
    {
        return $this->_dynamicPropertiesRule;
    }

    /**
     * @param callable $dynamicPropertiesRule
     * @return RoxOptionsBuilder
     */
    public function setDynamicPropertiesRule(callable $dynamicPropertiesRule)
    {
        $this->_dynamicPropertiesRule = $dynamicPropertiesRule;
        return $this;
    }
}
