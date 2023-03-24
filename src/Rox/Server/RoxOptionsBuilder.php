<?php

namespace Rox\Server;

use Kevinrob\GuzzleCache\Storage\CacheStorageInterface;

final class RoxOptionsBuilder
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
     * @var CacheStorageInterface|null $_cacheStorage
     */
    private $_cacheStorage;

    /**
     * @var bool $_logCacheHitsAndMisses
     */
    private $_logCacheHitsAndMisses = false;

    /**
     * @var int|null $_configFetchIntervalInSeconds
     */
    private $_configFetchIntervalInSeconds;

    /**
     * @var int
     */
    private $_timeout = 0;

    /**
     * @var NetworkConfigurationsOptions
     */
    private $_networkConfigurationsOptions;

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

    /**
     * @return CacheStorageInterface|null
     */
    public function getCacheStorage()
    {
        return $this->_cacheStorage;
    }

    /**
     * @return bool
     */
    public function isLogCacheHitsAndMisses()
    {
        return $this->_logCacheHitsAndMisses;
    }

    /**
     * @param bool $logCacheHitsAndMisses
     * @return RoxOptionsBuilder
     */
    public function setLogCacheHitsAndMisses($logCacheHitsAndMisses)
    {
        $this->_logCacheHitsAndMisses = $logCacheHitsAndMisses;
        return $this;
    }

    /**
     * @param CacheStorageInterface|null $cacheStrategy
     * @return RoxOptionsBuilder
     */
    public function setCacheStorage($cacheStrategy)
    {
        $this->_cacheStorage = $cacheStrategy;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getConfigFetchIntervalInSeconds()
    {
        return $this->_configFetchIntervalInSeconds;
    }

    /**
     * @param int|null $configFetchIntervalInSeconds
     * @return RoxOptionsBuilder
     */
    public function setConfigFetchIntervalInSeconds($configFetchIntervalInSeconds)
    {
        $this->_configFetchIntervalInSeconds = $configFetchIntervalInSeconds;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * @param int $timeout
     * @return RoxOptionsBuilder
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
        return $this;
    }

    /**
     * @return NetworkConfigurationsOptions
     */
    public function getNetworkConfigurationsOptions()
    {
        return $this->_networkConfigurationsOptions;
    }

    /**
     * @param NetworkConfigurationsOptions
     * @return RoxOptionsBuilder
     */
    public function setNetworkConfigurationsOptions($networkConfigurationsOptions)
    {
        $this->_networkConfigurationsOptions = $networkConfigurationsOptions;
        return $this;
    }

}
