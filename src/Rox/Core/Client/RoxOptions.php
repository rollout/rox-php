<?php

namespace Rox\Core\Client;

use Rox\Core\Configuration\ConfigurationFetchedEventHandlerInterface;
use Rox\Core\CustomProperties\DefaultDynamicPropertiesRule;
use Rox\Core\CustomProperties\DynamicPropertiesRuleInterface;
use Rox\Core\Impression\ImpressionEventHandlerInterface;

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
     * RoxOptions constructor.
     * @param RoxOptionsBuilder $builder
     */
    public function __construct(RoxOptionsBuilder $builder)
    {
        // TODO: initialize from $builder
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
     * @return int|null
     */
    function getFetchInterval()
    {
        return $this->_fetchInterval;
    }

    /**
     * @return ImpressionEventHandlerInterface|null
     */
    function getImpressionHandler()
    {
        return $this->_impressionHandler;
    }

    /**
     * @return ConfigurationFetchedEventHandlerInterface|null
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
     * @return DynamicPropertiesRuleInterface|null
     */
    function getDynamicPropertiesRule()
    {
        return $this->_dynamicPropertiesRule;
    }
}
