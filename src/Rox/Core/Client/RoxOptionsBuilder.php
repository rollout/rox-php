<?php

namespace Rox\Core\Client;

use Rox\Core\Configuration\ConfigurationFetchedEventHandlerInterface;
use Rox\Core\CustomProperties\DefaultDynamicPropertiesRule;
use Rox\Core\Impression\ImpressionEventHandlerInterface;

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
     * @var int|null
     */
    private $_fetchInterval;

    /**
     * @var ImpressionEventHandlerInterface|null
     */
    private $_impressionHandler;

    /**
     * @var ConfigurationFetchedEventHandlerInterface|null $_configurationFetchedHandler
     */
    private $_configurationFetchedHandler;

    /**
     * @var string|null $_roxyURL
     */
    private $_roxyURL;

    /**
     * @var DefaultDynamicPropertiesRule|null $_dynamicPropertiesRule
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
     * @return int|null
     */
    public function getFetchInterval()
    {
        return $this->_fetchInterval;
    }

    /**
     * @param int|null $fetchInterval
     * @return RoxOptionsBuilder
     */
    public function setFetchInterval($fetchInterval)
    {
        $this->_fetchInterval = $fetchInterval;
        return $this;
    }

    /**
     * @return ImpressionEventHandlerInterface|null
     */
    public function getImpressionHandler()
    {
        return $this->_impressionHandler;
    }

    /**
     * @param ImpressionEventHandlerInterface|null $impressionHandler
     * @return RoxOptionsBuilder
     */
    public function setImpressionHandler($impressionHandler)
    {
        $this->_impressionHandler = $impressionHandler;
        return $this;
    }

    /**
     * @return ConfigurationFetchedEventHandlerInterface
     */
    public function getConfigurationFetchedHandler()
    {
        return $this->_configurationFetchedHandler;
    }

    /**
     * @param ConfigurationFetchedEventHandlerInterface $configurationFetchedHandler
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
     * @return DefaultDynamicPropertiesRule|null
     */
    public function getDynamicPropertiesRule()
    {
        return $this->_dynamicPropertiesRule;
    }

    /**
     * @param DefaultDynamicPropertiesRule $dynamicPropertiesRule
     * @return RoxOptionsBuilder
     */
    public function setDynamicPropertiesRule($dynamicPropertiesRule)
    {
        $this->_dynamicPropertiesRule = $dynamicPropertiesRule;
        return $this;
    }
}
