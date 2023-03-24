<?php

namespace Rox\Server;

use Kevinrob\GuzzleCache\Storage\CacheStorageInterface;
use Rox\Core\Client\RoxOptionsInterface;

final class RoxOptions implements RoxOptionsInterface
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
     * @var callable|null
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
     * @var int|null
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
     * RoxOptions constructor.
     * @param RoxOptionsBuilder $roxOptionsBuilder
     */
    public function __construct(RoxOptionsBuilder $roxOptionsBuilder)
    {
        $this->_devModeKey = $roxOptionsBuilder->getDevModeKey();
        if (!$roxOptionsBuilder->getDevModeKey()) {
            $this->_devModeKey = "stam";
        }

        $this->_version = $roxOptionsBuilder->getVersion();
        if (!$roxOptionsBuilder->getVersion()) {
            $this->_version = "0.0";
        }

        $this->_impressionHandler = $roxOptionsBuilder->getImpressionHandler();
        $this->_configurationFetchedHandler = $roxOptionsBuilder->getConfigurationFetchedHandler();
        $this->_roxyURL = $roxOptionsBuilder->getRoxyURL();
        $this->_dynamicPropertiesRule = $roxOptionsBuilder->getDynamicPropertiesRule();
        $this->_cacheStorage = $roxOptionsBuilder->getCacheStorage();
        $this->_logCacheHitsAndMisses = $roxOptionsBuilder->isLogCacheHitsAndMisses();
        $this->_configFetchIntervalInSeconds = $roxOptionsBuilder->getConfigFetchIntervalInSeconds();
        $this->_timeout = $roxOptionsBuilder->getTimeout();
        $this->_networkConfigurationsOptions = $roxOptionsBuilder->getNetworkConfigurationsOptions();
    }

    /**
     * @return string
     */
    function getDevModeKey()
    {
        return $this->_devModeKey;
    }

    /**
     * @return string
     */
    function getVersion()
    {
        return $this->_version;
    }

    /**
     * @return callable|null
     */
    function getImpressionHandler()
    {
        return $this->_impressionHandler;
    }

    /**
     * @return callable|null
     */
    function getConfigurationFetchedHandler()
    {
        return $this->_configurationFetchedHandler;
    }

    /**
     * @return string|null
     */
    function getRoxyURL()
    {
        return $this->_roxyURL;
    }

    /**
     * @return callable|null
     */
    function getDynamicPropertiesRule()
    {
        return $this->_dynamicPropertiesRule;
    }

    /**
     * @return CacheStorageInterface|void|null
     */
    function getCacheStorage()
    {
        return $this->_cacheStorage;
    }

    /**
     * @return bool
     */
    function isLogCacheHitsAndMisses()
    {
        return $this->_logCacheHitsAndMisses;
    }

    /**
     * @return int|null
     */
    function getConfigFetchIntervalInSeconds()
    {
        return $this->_configFetchIntervalInSeconds;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }

    /**
     * @return NetworkConfigurationsOptions
     */
    public function getNetworkConfigurationsOptions()
    {
        return $this->_networkConfigurationsOptions;
    }
}
