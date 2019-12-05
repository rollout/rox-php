<?php

namespace Rox\Core\Client;

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
