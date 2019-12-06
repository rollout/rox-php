<?php

namespace Rox\Server;

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
}
