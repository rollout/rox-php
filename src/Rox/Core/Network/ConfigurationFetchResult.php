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
     * @var array $_parsedData
     */
    private $_parsedData;

    /**
     * @var bool $_isFromCache
     */
    private $_isFromCache;

    /**
     * ConfigurationFetchResult constructor.
     * @param array $parsedData
     * @param int $source
     * @param bool $isFromCache
     */
    public function __construct($parsedData, $source, $isFromCache = false)
    {
        $this->_source = $source;
        $this->_parsedData = $parsedData;
        $this->_isFromCache = $isFromCache;
    }

    /**
     * @return int
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @return array
     */
    public function getParsedData()
    {
        return $this->_parsedData;
    }

    /**
     * @return bool
     */
    public function isFromCache()
    {
        return $this->_isFromCache;
    }

    /**
     * @param ConfigurationFetchResult $other
     * @return bool
     */
    public function equals(ConfigurationFetchResult $other)
    {
        return $this->_parsedData != null && $other->_parsedData != null &&
            json_encode($this->_parsedData) === json_encode($other->_parsedData);
    }
}
