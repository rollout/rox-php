<?php

namespace Rox\Core\Network;

class ConfigurationFetchResult
{
    /**
     * @var int $_source
     * @see ConfigurationSource
     */
    private $_source;

    /**
     * @var object $_parsedData
     */
    private $_parsedData;

    /**
     * ConfigurationFetchResult constructor.
     * @param object $parsedData
     * @param int $source
     */
    public function __construct($parsedData, $source)
    {
        $this->_source = $source;
        $this->_parsedData = $parsedData;
    }

    /**
     * @return int
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @return object
     */
    public function getParsedData()
    {
        return $this->_parsedData;
    }

    /**
     * @param ConfigurationFetchResult $other
     * @return bool
     */
    public function equals(ConfigurationFetchResult $other)
    {
        return $this->_parsedData != null && $other->_parsedData != null &&
            json_encode($this->_parsedData) == json_encode($other->_parsedData);
    }
}
