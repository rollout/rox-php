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
     * @var string $_cacheStatus
     * @see CacheStatus
     */
    private $_cacheStatus;

    /**
     * ConfigurationFetchResult constructor.
     * @param array $parsedData
     * @param int $source
     * @param string $cacheStatus
     * @see CacheStatus
     */
    public function __construct($parsedData, $source, $cacheStatus = CacheStatus::MISS)
    {
        $this->_source = $source;
        $this->_parsedData = $parsedData;
        $this->_cacheStatus = $cacheStatus;
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
        return CacheStatus::isFromCache($this->_cacheStatus);
    }

    /**
     * @return bool
     */
    public function isContentUnchanged()
    {
        return CacheStatus::isContentUnchanged($this->_cacheStatus);
    }
}
