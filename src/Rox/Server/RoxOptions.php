<?php

namespace Rox\Server;

use Kevinrob\GuzzleCache\Storage\CacheStorageInterface;
use Ramsey\Uuid\Uuid;
use Rox\Core\Client\RoxOptionsInterface;
use Rox\Core\Logging\LoggerFactory;

class RoxOptions implements RoxOptionsInterface
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
     * @var string|null
     */
    private $_distinctId;

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

        if ($roxOptionsBuilder->getLoggerFactory() != null) {
            LoggerFactory::setup($roxOptionsBuilder->getLoggerFactory());
        }

        $this->_impressionHandler = $roxOptionsBuilder->getImpressionHandler();
        $this->_configurationFetchedHandler = $roxOptionsBuilder->getConfigurationFetchedHandler();
        $this->_roxyURL = $roxOptionsBuilder->getRoxyURL();
        $this->_dynamicPropertiesRule = $roxOptionsBuilder->getDynamicPropertiesRule();
        $this->_cacheStorage = $roxOptionsBuilder->getCacheStorage();
        $this->_logCacheHitsAndMisses = $roxOptionsBuilder->isLogCacheHitsAndMisses();
        $this->_configFetchIntervalInSeconds = $roxOptionsBuilder->getConfigFetchIntervalInSeconds();

        if ($roxOptionsBuilder->getDistinctId() != null) {
            $this->_distinctId = $roxOptionsBuilder->getDistinctId();
        } else {
            try {
                $this->_distinctId = Uuid::uuid4()->toString();
            } catch (\Exception $e) {
                $this->_distinctId = uniqid('rox-php-sdk');
            }
        }
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
     * @return string|null
     */
    function getDistinctId()
    {
        return $this->_distinctId;
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
}
